<?php

class Admin_Tags_Controller extends Base_Controller {

	public $restful = true;

	public function __construct()
	{
	    parent::__construct();
	    $this->filter('before', 'csrf')->on('post');
	    $this->filter('before', 'auth');

		if (Auth::check()) { // auth check to avoid trying to get property of non object error. dirty fix.
			// validate if the user is an admin
		    $userGroup = UserGroup::find(Auth::user()->user_groups_id);

		    if ($userGroup->group_name != 'admin') {
		    	die('cheating????! You are not allowed here.');
		    }
		}
	}

	public function get_add()
	{
		return View::make('admin.tags.add');
	}

	public function post_add()
	{
		$tag = New Tag;
		$tag->name = Input::get('name');
		$tag->slug = Str::slug(Input::get('name'));
		$tag->user_id = Auth::user()->id;
		$tag->ip = Request::ip();

		if ($tag->save()) {
			return Redirect::back()->with('success', 'Tag successfuly added!');	
		} else {
			return Redirect::back()->with_errors($tag->errors->all());
		}
		
	}

	public function get_manage()
	{
		return View::make('admin.tags.index')
					->with('tags', Tag::order_by('created_at', 'desc')->paginate(10));
	}

	public function get_edit($id)
	{
		// validate
		if ($count = Tag::where_id($id)->count() < 1) {
			return Response::error('404');
		}

		return View::make('admin.tags.edit')
					->with('tag', $tag = Tag::find($id));
	}

	public function post_edit()
	{
		$tag = Tag::find(Input::get('id'));

		$tag->name = Input::get('name');
		$tag->slug = Str::slug(Input::get('name'));

		if ($tag->save(Tag::$rules = array( // to override existing rules
										'name' => 'required|max:25|unique:tags,name,' . $tag->id,
										'slug' => 'required',
										'user_id' => 'required|integer',
										'ip' => 'required',
									)
		)) {
			return Redirect::back()->with('success', 'Tag successfully updated!');
		}

		return Redirect::back()->with_errors( $tag->errors->all() );
	}
	
	public function get_delete($id)
	{
		// return $id;
		$tag = Tag::find($id);

		// delete relationship between the tag and the snippets
		$tag->snippets()->delete();

		// return $snippet->title;

	 	$fromSingleView = Input::get('fromSingleView');

	 	// return $fromSingleView;

		if ($tag->delete()) {

			if ($fromSingleView == 1) {
				return Redirect::to_action('admin.snippets@index')->with('success', 'Snippet successfuly deleted!');
			}

			return Redirect::back()->with('success', 'Tag successfuly deleted!');	

		} else {

			if ($fromSingleView == 1) {
				return Redirect::to_action('admin.snippets@index')->with_errors(array('Deleting failed!'));
			}
			
			return Redirect::back()->with_errors(array('Deleting failed!'));


		}
	}


	


}