<?php

namespace App\Http\Traits;

use Http;

trait TribeHiredApi {
	public function fetchPosts() {
		return Http::get('https://jsonplaceholder.typicode.com/posts')
			->json();
	}

	public function fetchComments() {
		return Http::get('https://jsonplaceholder.typicode.com/comments')
			->json();
	}
}