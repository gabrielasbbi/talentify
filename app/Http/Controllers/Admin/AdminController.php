<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Opportunities;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Spatie\Searchable\Search;

class AdminController extends Controller {


    /**
     * Opportunities Model
     * @var Opportunities
     */
    protected $opportunity;

    /**
     * Inject the models.
     * @param Opportunities $opportunity
     */
    public function __construct(Opportunities $opportunity)
    {
        $this->opportunity = $opportunity;
    }

    /**
     * Show a list of all the opportunities.
     *
     * @return View
     */
    public function index()
    {
        // Grab all the opportunities
        $opportunities = $this->opportunity;

        $searchResults = $this->paginate((new Search())
            ->registerModel(Opportunities::class, ['title', 'description', 'status', 'workplace', 'salary'])
            ->search('active'));

        return view('admin.home', compact('searchResults'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $searchResults = $this->paginate((new Search())
            ->registerModel(Opportunities::class, ['title', 'description', 'status', 'workplace', 'salary'])
            ->perform($request->input('query')));

        return view('admin.home', compact('searchResults'));
    }

    /**
     * @param $items
     * @param int $perPage
     * @param null $page
     * @param array $options
     * @return LengthAwarePaginator
     */
    public function paginate($items, $perPage = 24, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $totalCount = count($items);
        $pageItems= $items->forPage($page, $perPage);
        $paginator = new LengthAwarePaginator($pageItems, $totalCount, $perPage, $page);

        return $paginator;
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function getCreate()
    {
        // Show the page
        return View::make('admin/opportunities/create_edit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postCreate()
    {
        // Declare the rules for the form validation
        $rules = [
            'title'       => 'required|min:3',
            'description' => 'required|min:3',
            'status'      => 'in:active,paused,inactive',
        ];

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Create a new opportunity
            $this->opportunity->title       = Input::get('title');
            $this->opportunity->description = Input::get('description');
            $this->opportunity->status      = Input::get('status');
            $this->opportunity->workplace   = Input::get('workplace');
            $this->opportunity->salary      = Input::get('salary');

            // Was the opportunity created?
            if($this->opportunity->save())
            {
                // Redirect to the new opportunity page
                return Redirect::to('admin/opportunity/' . $this->opportunity->id . '/edit')->with('success', 'Job opportunity created with success!');
            }

            // Redirect to the opportunity create page
            return Redirect::to('admin/opportunity/create')->with('error', 'An error occurred while creating a new job opportunity.');
        }

        // Form validation failed
        return Redirect::to('admin/opportunity/create')->withInput()->withErrors($validator);
    }

    /**
     * Display the specified resource.
     *
     * @param $opportunity
     * @return Response
     */
    public function getShow($opportunity)
    {
        // redirect to the frontend
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $opportunity
     * @return Response
     */
    public function getEdit($opportunity)
    {
        // Title
        $title = Lang::get('admin/blogs/title.blog_update');

        // Show the page
        return View::make('admin/blogs/create_edit', 'Create job opportunity');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $opportunity
     * @return Response
     */
    public function postEdit($opportunity)
    {

        // Declare the rules for the form validation
        $rules = [
            'title'       => 'required|min:3',
            'description' => 'required|min:3',
            'status'      => 'in:active,paused,inactive',
        ];

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            // Update the blog post data
            $this->opportunity->title       = Input::get('title');
            $this->opportunity->description = Input::get('description');
            $this->opportunity->status      = Input::get('status');
            $this->opportunity->workplace   = Input::get('workplace');
            $this->opportunity->salary      = Input::get('salary');

            // Was the opportunity updated?
            if($opportunity->save())
            {
                // Redirect to the new opportunity page
                return Redirect::to('admin/opportunity/' . $opportunity->id . '/edit')->with('success', 'Job opportunity edited with success!');
            }

            // Redirect to the opportunity management page
            return Redirect::to('admin/opportunity/' . $opportunity->id . '/edit')->with('error', 'An error occurred while editing the job opportunity.');
        }

        // Form validation failed
        return Redirect::to('admin/opportunity/' . $opportunity->id . '/edit')->withInput()->withErrors($validator);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $opportunity
     * @return Response
     */
    public function getDelete($opportunity)
    {
        return View::make('admin/opportunity/delete', 'Delete job opportunity');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $opportunity
     * @return Response
     */
    public function postDelete($opportunity)
    {
        // Declare the rules for the form validation
        $rules = array(
            'id' => 'required|integer'
        );

        // Validate the inputs
        $validator = Validator::make(Input::all(), $rules);

        // Check if the form validates with success
        if ($validator->passes())
        {
            $id = $opportunity->id;
            $opportunity->delete();

            // Was the opportunity deleted?
            $opportunity = Opportunities::find($id);
            if(empty($opportunity))
            {
                // Redirect to the opportunities management page
                return Redirect::to('admin/opportunities')->with('success', 'Job opportunity deleted with success!');
            }
        }
        // There was a problem deleting the opportunity
        return Redirect::to('admin/opportunities')->with('error', 'An error occurred while deleting the job opportunity.');
    }

    /**
     * Show a list of all the opportunities formatted for Datatables.
     *
     * @return Datatables JSON
     */
    public function getData()
    {
        $opportunities = Opportunities::select(array('opportunities.id', 'opportunities.title', 'opportunities.description', 'opportunities.status', 'opportunities.workplace', 'opportunities.salary', 'posts.created_at'));

        return Datatables::of($opportunities)
            ->edit_column('created_at', '{{ $created_at->format("Y-m-d h:i:s") }}')
            ->add_column('actions', '
            <div class="btn-group">
                <a href="{{{ URL::to(\'admin/opportunity/\' . $id . \'/edit\' ) }}}" class="btn btn-primary btn-xs iframe" ><i class="fa fa-pencil"></i> Edit </a>
                <a href="{{{ URL::to(\'admin/opportunity/\' . $id . \'/delete\' ) }}}" class="btn btn-xs btn-danger iframe"><i class="fa fa-trash-o"></i> Delete </a>
            </div>
            ')
            ->remove_column('id')
            ->remove_column('rn') // rownum for oracle
            ->make();
    }
}
