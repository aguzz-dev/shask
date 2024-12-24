<?php

namespace App\Jobs;

use Exception;
use App\Database;
use App\Models\PublicPost;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StoreQuestionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $publicPostId;
    public $text;
    public $hint;

    /**
     * Crea una nueva instancia del Job.
     *
     * @param int $publicPostId
     * @param string $text
     * @param string $hint
     */
    public function __construct(int $publicPostId, string $text, string $hint)
    {
        $this->publicPostId = $publicPostId;
        $this->text = $text;
        $this->hint = $hint;
    }

    /**
     * Ejecuta el Job.
     */
    public function handle()
    {
        $isPublicPostExist = (new PublicPost)->findById($this->publicPostId);
        if (!$isPublicPostExist) {
            throw new \Exception('Post público no encontrado', 404);
        }

        (new Database)->query("INSERT INTO `questions` (public_post_id, text, hint) VALUES ({$this->publicPostId}, '{$this->text}', '{$this->hint}')");

        return [
            'id' => (new Database)->dbConnection->insert_id,
            'text' => $this->text,
            'id_post' => $this->publicPostId
        ];
    }
}
