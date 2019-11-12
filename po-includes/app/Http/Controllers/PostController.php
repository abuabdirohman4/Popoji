<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artesaos\SEOTools\Facades\SEOTools;

use App\Post;

class PostController extends Controller
{
	/**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application post.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($seotitle)
    {
		
	}
	
	/**
     * Show the application search post.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
	public function search(Request $request)
    {
		$validator = $this->validate($request,[
			'terms' => 'required|max:255',
		]);
		
		$terms = strip_tags($request->terms);
		
		$twitterid = explode('/', getSetting('twitter'));
		SEOTools::setTitle($terms.' - '.getSetting('web_name'));
		SEOTools::setDescription($terms.' - '.getSetting('web_description'));
		SEOTools::metatags()->setKeywords(explode(',', getSetting('web_keyword')));
		SEOTools::setCanonical(getSetting('web_url'));
		SEOTools::opengraph()->setTitle($terms.' - '.getSetting('web_name'));
		SEOTools::opengraph()->setDescription($terms.' - '.getSetting('web_description'));
		SEOTools::opengraph()->setUrl(getSetting('web_url'));
		SEOTools::opengraph()->setSiteName(getSetting('web_author'));
		SEOTools::opengraph()->addImage(asset('po-content/uploads/'.getSetting('logo')));
		SEOTools::twitter()->setSite('@'.$twitterid[count($twitterid)-1]);
		SEOTools::twitter()->setTitle($terms.' - '.getSetting('web_name'));
		SEOTools::twitter()->setDescription($terms.' - '.getSetting('web_description'));
		SEOTools::twitter()->setUrl(getSetting('web_url'));
		SEOTools::twitter()->setImage(asset('po-content/uploads/'.getSetting('logo')));
		SEOTools::jsonLd()->setTitle($terms.' - '.getSetting('web_name'));
		SEOTools::jsonLd()->setDescription($terms.' - '.getSetting('web_description'));
		SEOTools::jsonLd()->setType('WebPage');
		SEOTools::jsonLd()->setUrl(getSetting('web_url'));
		SEOTools::jsonLd()->setImage(asset('po-content/uploads/'.getSetting('logo')));
		
		$posts = Post::leftJoin('users', 'users.id', 'posts.created_by')
			->leftJoin('categories', 'categories.id', 'posts.category_id')
			->where([
				['posts.title', 'LIKE', '%'.$terms.'%'],
				['posts.active', '=', 'Y']
			])
			->orWhere([
				['posts.content', 'LIKE', '%'.$terms.'%'],
				['posts.tag', 'LIKE', '%'.$terms.'%']
			])
			->select('posts.*', 'categories.title as ctitle', 'users.name')
			->orderBy('posts.id', 'desc')
			->paginate(5);
		
		$posts->appends(['terms' => $terms]);
		
		return view(getTheme('search'), compact('terms', 'posts'));
    }
}