<?php
namespace App\Models;

use App\Database;

class PublicPost extends Database
{
    protected $table = 'public_posts';

    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE post_id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllUrls()
    {
        $urls = $this->query("SELECT url FROM {$this->table}")->fetch_all(MYSQLI_ASSOC);
        $urlsFormatted = array_map(function($item) {
            return $item["url"];
        }, $urls);
        return $urlsFormatted;
    }

    public function getPostIdByUrl($url)
    {
        $post = $this->query("SELECT id FROM {$this->table} WHERE url = '{$url}'")->fetch_all(MYSQLI_ASSOC);
        return $post[0]['id'];
    }

    public function getPublicPostByPostId($postId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = {$postId}";
        return $this->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getPostDataByUrl($url)
    {
        $post = $this->query("SELECT * FROM {$this->table}
            LEFT JOIN posts ON posts.id = public_posts.post_id
            WHERE url = '{$url}'")->fetch_all(MYSQLI_ASSOC);
        if(!$post){
            return false;
        }
        return $post[0];
    }

    public function makePublicPost($id)
    {
        $post = (new Post)->findById($id);
        if(!$post){
            throw new \Exception('Post no encontrado', 404);
        }
        $userId = (new Post)->findById($id)[0]['user_id'];
        $url    = $this->generateRandomUrl();

        $isPostAlreadyPublished = (new PublicPost)->getPublicPostByPostId($id);
        if ($isPostAlreadyPublished){
            $post[0]['url'] = $isPostAlreadyPublished[0]['url'];
            return $post;
        }

        $post[0]['url'] = $url;
        $this->query("UPDATE `posts` SET status = 1 WHERE id = {$id}");

        $this->query("INSERT INTO `{$this->table}` (`post_id`,`user_id`,`url`) VALUES ('{$id}', '{$userId}', '{$url}')");
        return $post;
    }

    public function makePrivatePost($id)
    {
        $publicPost = $this->findById($id);
        $idPost = $publicPost[0]['post_id'];
        if(!$publicPost){
            throw new \Exception('Post no encontrado', 404);
        }
        $this->query("UPDATE `posts` SET `status` = '0' WHERE id = {$idPost}");
        $this->query("DELETE FROM {$this->table} WHERE id = {$id}");
        $post = (new Post)->findById($idPost);
        return $post;
    }

    public static function generateRandomUrl() {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $url = '';
        for ($i = 0; $i < 4; $i++) {
            $url .= $chars[rand(0, 61)];
        }
        return $url;
    }
}
