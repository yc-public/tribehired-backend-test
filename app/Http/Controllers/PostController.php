<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\AG\Notification\PaymentJson;
use App\Http\Traits\TribeHiredApi;

use Log;
use Exception;

class PostController extends Controller
{
	use TribeHiredApi;

	public function popular() : string {
		try {

		} catch(Exception $e) {
			Log::error($e->getMessage());
			return '500, Something error in our server';
		}
		// fetching comments from API
		$comments = $this->fetchComments();
		
		// grouped comments by "postId"
		// count comments associated with "postId"
		$grouped = collect($comments)
		->groupBy('postId')
		->transform(function($comments) {
			return $comments->values()->count();
		});

		// fetching posts from API
		$posts = $this->fetchPosts();

		// obtain the count value from grouped array by "postId" index
		// merging "total_numbers_of_comments" into post item 
		$merged = collect($posts)
		->transform(function($post) use($grouped) {
			$commentCount = $grouped[$post['id']];

			return [
				'post_id' => $post['id'],
				'post_title' => $post['title'],
				'post_body' => $post['body'],
				'total_number_of_comments' => $commentCount
			]; 
		});

		// sort by highest "total_numbers_of_comments"
		// then, sort by ID ascending
		// obtain the top 5 results
		$sorted = $merged->sortBy(function ($post, $key) {
			return $post['total_number_of_comments'];
		})
		->sortBy(function ($post, $key) {
			return $post['post_id'];
		})
		->take(5);

		// returned the API response
		return '<pre>' . $sorted->values()->toJson(JSON_PRETTY_PRINT) . '</pre>';
	}

	public function search(Request $request) {
		try {
			// fetching comments from API
			$comments = $this->fetchComments();

			// intersect the request 'query_string' from internal 'query_string' key
			$available_keys = collect(['postId', 'id', 'name', 'email', 'body']);
			$intersected = $available_keys->intersect($request->keys());

			// loop through the intersect keys and filter the result
			// map the filtered result array into "filtered"
			$filtered = collect($comments);

			$intersected->each(function($key) use($request, $comments, &$filtered) {
				$filtered = $filtered->filter(function ($comment) use($request, $key) {
					return stristr($comment[$key], $request[$key]) !== false;
				});
			});
		} catch(Exception $e) {
			Log::error($e->getMessage());
			return '500, Something error in our server';
		}

		// returned the API response
		return '<pre>' . $filtered->values()->toJson(JSON_PRETTY_PRINT) . '</pre>';
	}
}
