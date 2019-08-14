<?php

namespace MeCodeNinja\GitHubWebhooks\GitHub;

use Illuminate\Support\Facades\DB;

class Helper
{
    /**
     * Get the stored User Token
     *
     * @param string $repoName
     * @return mixed|null
     */
    public function getUserToken(string $repoName) {
        $record = DB::table('repositories')
            ->join('github_users','repositories.user_id','=','github_users.user_id')
            ->where('name','=',$repoName)
            ->first();

        if (empty($record)) {
            return null;
        }

        return $record->token;
    }
}