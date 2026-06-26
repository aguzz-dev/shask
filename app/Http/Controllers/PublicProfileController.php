<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Asset;
use App\Models\PublicAsset;
use App\Models\Achievement;
use App\Models\UserStats;

class PublicProfileController extends Controller
{
    public function show($username)
    {
        $username = preg_replace('/[^a-zA-Z0-9_.\-]/', '', $username);
        $rows = (new User)->query(
            "SELECT id, username, avatar, bio FROM users WHERE username = '{$username}'"
        )->fetch_all(MYSQLI_ASSOC);

        if (!$rows) {
            return response()->view('errors.404', [], 404);
        }
        $user = $rows[0];
        $userId = (int) $user['id'];

        // Evalúa y persiste logros también desde la web (spec 1.2).
        $stats = (new UserStats)->forUser($userId);
        $achievementModel = new Achievement;
        $achievementModel->evaluate($userId, $stats);
        $unlocked = array_values(array_filter(
            $achievementModel->listFor($userId, 'es'),
            fn ($a) => $a['unlocked']
        ));

        // Buzones habilitados: post vivo (status=1) y no vencido (3 días).
        $mailboxes = (new UserStats)->query(
            "SELECT pp.url, p.title, p.asset_id
             FROM public_posts pp
             JOIN posts p ON p.id = pp.post_id
             WHERE pp.user_id = {$userId} AND p.status = 1
               AND p.created_at >= DATE_SUB(CURDATE(), INTERVAL 3 DAY)"
        )->fetch_all(MYSQLI_ASSOC);

        foreach ($mailboxes as &$mailbox) {
            $asset = $mailbox['asset_id'] > 10000
                ? ((new Asset)->findById($mailbox['asset_id'])[0] ?? null)
                : ((new PublicAsset)->findById($mailbox['asset_id'])[0] ?? null);
            $mailbox['colors'] = $asset ? (json_decode($asset['color']) ?? []) : [];
            $mailbox['icon']   = $asset['icon'] ?? null;
        }

        return view('PublicProfile', [
            'username'     => $user['username'],
            'avatar'       => json_decode($user['avatar']),
            'bio'          => $user['bio'] ?? null,
            'achievements' => $unlocked,
            'mailboxes'    => $mailboxes,
        ]);
    }
}
