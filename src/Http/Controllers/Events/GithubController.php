<?php

namespace MeCodeNinja\GitHubWebhooks\Http\Controllers\Events;

use MeCodeNinja\GitHubWebhooks\Events\PullRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GithubController extends Controller
{
    public function index(Request $request)
    {
        /**
         * We only care about pull_request for now
         */
        if ($request->hasHeader('X-GitHub-Event')) {
            $githubEvent = $request->header('X-GitHub-Event');
            if ($githubEvent != 'pull_request') {
                return Response::HTTP_OK;
            }
        }

        /**
         * Make sure the request has some content to work with
         */
        $content = $request->getContent();
        if (empty($content)) {
            return Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        /**
         * Dispatch PullRequest Event
         */
        event(new PullRequest($request));

        return Response::HTTP_OK;
    }
}