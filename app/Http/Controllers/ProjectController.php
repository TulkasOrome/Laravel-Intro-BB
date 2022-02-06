<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Project;
use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;


class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        View::share('active_menu_item', 'projects');
    }

    public function index(Request $request){
        // redirect staff to dashboard
        if($request->User()->currentTeam()->pivot->role == 'staff'){ return redirect('/dashboard'); }

        return view('project.projects')->with('deleted', $request->input('project_deleted', false));
    }

    public function show(Request $request, Project $project){
        $project->recalculate_cn();
        $project->recalculate_labor_cn();

        $project->client = $project->client()->get();
        $project->documents = $project->documents()->where('document_type', 'document')->get();
        $project->photos = $project->documents()->where('document_type', 'photo')->get();
        $project->jobs = $project->jobs()->get();
        // $project->wages = $project->wages();
        // $project->stage_wages = $project->all_stage_wages();
        $project->wages = $project->wages();
        $project->stage_wages = [];
        $project->material_costs_total = $project->total_material_cost();
        $project->operating_percentage = $project->operating_percentage();

        $stage_timesheets = $project->timesheets_by_stage();

        $stages = array();

        foreach($stage_timesheets as $stage => $sheets){
            if(!isset($stages[$stage])){
                $stages[$stage] = array();
            }

            foreach($sheets as $ts){
                if(!isset($stages[$stage][$ts->job->start_date])){
                    $stages[$stage][$ts->job->start_date] = array();
                }

                $stages[$stage][$ts->job->start_date][] = $ts;
            }
        }


        $project->stage_timesheets = $stages;

        if ($project->project_type == 0) {

            return view('project.project')->with(array('project' => $project, 'role' => $request->User()->currentTeam()->pivot->role));
        }

        else {

            return view('project.project-materials')->with(array('project' => $project, 'role' => $request->User()->currentTeam()->pivot->role));

        }
    }

    public function comment(Request $request, Project $project){
        $this->validate($request, ['comment' => 'required']);

        $comment = new Comment;
        $comment->job_id = NULL;
        $comment->project_id = $request->project_id;
        $comment->user_id = $request->User()->id;
        $comment->comments = $request->comment ? $request->comment : '';

        $comment->save();

        return $comment;
    }

    public function single(Request $request, Project $project){
        if($request->attach_client){
            $project->client = $project->client()->get()->first();
        }

        if($request->attach_documents){
            $project->documents = $project->documents()->where('document_type', 'document')->get();
        }

        return $project;
    }



    public function all(Request $request){

        if($request->User()->currentTeam()->pivot->role == 'staff'){
            return array();
        }

        $request->merge([ 'attach_client' => true ]);


        $projects = $request->User()->currentTeam->projects()->orderBy("name");

        if($request->User()->currentTeam()->pivot->role == 'manager'){
            $projects = $projects->where('manager_id', $request->User()->id);
        }

        $projects = $projects->get();

        if(!$request->attach_client){ return $projects; }

        foreach($projects as $project){
            $project->client = $project->client()->get()->first();
        }

        return $projects;
    }

    public function get_recent(Request $request, $user_id = false){
        if($request->User()->currentTeam()->pivot->role == 'staff'){
            return array();
        }

        $request->merge([ 'attach_client' => true ]);

        $projects = $request->User()->currentTeam->projects()->orderBy("name");
        $projects->whereDate('date_completed', '>', Carbon::today()->submonths(2));
        $projects->where('active', 4);

        if($user_id !== false){
            $projects->where('manager_id', $user_id);
        }

        $projects = $projects->get();

        if(!$request->attach_client){ return $projects; }

        foreach($projects as $project){
            $project->client = $project->client()->get()->first();
        }

        return $projects;
    }

    public function get_by_status(Request $request, $status_string = 'active', $user_id = false){
        //var project_types = ['all/archived', 'all/pending', 'all/scheduled', 'all/in_progress', 'all/completed', 'all/active', 'all'];
        // OLD  $status_array = array( 'archived' => 0, 'pending' => 1, 'scheduled' => 2, 'in_progress' => 3, 'completed' => 4 );
        $status_array = array( 'archived' => 0, 'pending' => 1, 'scheduled' => 2, 'in_progress' => 3, 'completed' => 4 );

        if($request->User()->currentTeam()->pivot->role == 'staff'){
            return array();
        }

        $request->merge([ 'attach_client' => true ]);
        $request->input('recent', false);

        $projects = $request->User()->currentTeam->projects()->orderBy("name");

        if($user_id !== false){
            $projects->where('manager_id', $user_id);
        }

        if($request->recent) {
            $projects->whereDate('date_completed', '>', Carbon::today()->submonths(2));
        }

        if(isset($status_array[$status_string])){
            $projects->where('active', $status_array[$status_string]);
        }
        else {
            $projects->where('active', '!=', '0');	// archived
            $projects->where('active', '!=', '4');	// completed
        }

        if($request->User()->currentTeam()->pivot->role == 'manager'){
            $projects = $projects->where('manager_id', $request->User()->id);
        }
        $projects = $projects->get();

        if(!$request->attach_client){ return $projects; }

        foreach($projects as $project){
            $project->client = $project->client()->get()->first();
        }

        return $projects;
    }

    public function active(Request $request){
        if($request->User()->currentTeam()->pivot->role == 'staff'){
            return array();
        }

        $projects = $request->User()->currentTeam->projects()->where('active', 1)->orderBy("name");
        if($request->User()->currentTeam()->pivot->role == 'manager'){
            $projects = $projects->where('manager_id', $request->User()->id);
        }
        $projects = $projects->get();

        if(!$request->attach_client){ return $projects; }

        foreach($projects as $project){
            $project->client = $project->client()->get()->first();
        }

        return $projects;
    }

    public function inactive(Request $request){
        if($request->User()->currentTeam()->pivot->role == 'staff'){
            return array();
        }

        $projects = $request->User()->currentTeam->projects()->where('active', 0)->orderBy("name");
        if($request->User()->currentTeam()->pivot->role == 'manager'){
            $projects = $projects->where('manager_id', $request->User()->id);
        }
        $projects = $projects->get();

        if(!$request->attach_client){ return $projects; }

        foreach($projects as $project){
            $project->client = $project->client()->get()->first();
        }

        return $projects;
    }

    public function user_projects(Request $request, $user_id, $status_string = 'active'){

        if($request->User()->currentTeam->member($user_id)->count() <= 0){
            return [];
        }

        return $this->get_by_status($request, $status_string, $user_id);
    }

    public function user_recent_projects(Request $request, $user_id = null){

        if($request->User()->currentTeam->member($user_id)->count() <= 0){
            return [];
        }

        return $this->get_recent($request, $user);
    }

    public function store(Request $request){
        $this->validate($request, [
            'name' => 'required|max:255',
            'client_id' => 'required|integer|exists:team_clients,id',
            'active' => 'required|integer',
            //   'start_date' => 'nullable|date',
            //   'end_date' => 'nullable|date|after_or_equal:start_date',
            'cost_materials' => 'nullable|numeric',
            'material_budget' => 'nullable|numeric',
            'job_value' => 'nullable|numeric',
            'contract_value' => 'nullable|numeric',
            'labor_allowance' => 'nullable|numeric',
            'cost_labor' => 'nullable|numeric',
            'total_area' => 'nullable|numeric',
            // 'brick_count_floor_0' => 'nullable|integer',
            // 'brick_count_floor_1' => 'nullable|integer',
            // 'brick_count_floor_2' => 'nullable|integer',
            // 'brick_count_floor_3' => 'nullable|integer',
            // 'brick_type' => ['regex:/^(austral|pgh|recycled|common|other)$/'],
            // 'brick_color' => ['regex:/^(standard|common|other)$/'],
            'brick_type' => 'required|max:255',
            'brick_color' => 'required|max:255',
            'cement_color' => ['regex:/^(white|natural|oxide|other)$/'],
            'manager_id' => 'nullable|integer|exists:users,id',
        ]);

        $project = new Project;


        $project->client_id = $request->client_id;
        $project->team_id = $request->User()->currentTeam->id;

        $project->name = $request->name ? $request->name : "";
        $project->address_line_1 = $request->address_line_1 ? $request->address_line_1 : "";
        $project->address_line_2 = $request->address_line_2 ? $request->address_line_2 : "";
        $project->address_suburb = $request->address_suburb ? $request->address_suburb : "";
        $project->address_state = $request->address_state ? $request->address_state : "";
        $project->address_postcode = $request->address_postcode ? $request->address_postcode : "";

        $project->active = $request->active ? $request->active : 1;
        $project->po_number = $request->po_number ? $request->po_number : "";
        $project->cost_materials = $request->cost_materials ? $request->cost_materials : 0.00;
        $project->job_value = $request->job_value ? $request->job_value : 0.00;
        $project->labor_allowance = $request->labor_allowance ? $request->labor_allowance: 0.00;
        $project->contract_value = $request->contract_value ? $request->contract_value : 0.00;
        $project->cost_labor = $request->cost_labor ? $request->cost_labor : 0.00;
        $project->labor_budget = $request->labor_budget ? $request->labor_budget : 70.00;
        $project->material_budget = $request->material_budget ? $request->material_budget: 0.00;
        // $project->total_area = $request->total_area ? $request->total_area : 0.00;
        // $project->brick_count_floor_0 = $request->brick_count_floor_0 ? $request->brick_count_floor_0 : 0;
        // $project->brick_count_floor_1 = $request->brick_count_floor_1 ? $request->brick_count_floor_1 : 0;
        // $project->brick_count_floor_2 = $request->brick_count_floor_2 ? $request->brick_count_floor_2 : 0;
        // $project->brick_count_floor_3 = $request->brick_count_floor_3 ? $request->brick_count_floor_3 : 0;
        $project->cement_color = $request->cement_color ? $request->cement_color : "natural";
        $project->brick_type = $request->brick_type ? $request->brick_type : "common";
        $project->brick_color = $request->brick_color ? $request->brick_color : "common";

        $project->project_notes = $request->project_notes ? $request->project_notes : "";
        $project->budget_note = $request->budget_note ? $request->budget_note : "";

        $project->supervisor_name = $request->supervisor_name ? $request->supervisor_name : "";
        $project->supervisor_phone = $request->supervisor_phone ? $request->supervisor_phone : "";
        $project->supervisor_email = $request->supervisor_email ? $request->supervisor_email : "";

        $project->job_type = $request->job_type ? $request->job_type : "";
        $project->project_type = $request->project_type ? $request->project_type : 0;

        $project->date_completed = NULL;

        $stages = ($request->stages) ? $request->stages : array();
        if(count($request->stages)){
            foreach($stages as $i => $stage){
                if($stage['estimated_start_date'] && $stage['estimated_start_date'] != NULL && $stage['estimated_start_date'] != ''){
                    $stages[$i]['estimated_start_date'] = $stage['estimated_start_date'];
                }
                else{
                    $stages[$i]['estimated_start_date'] = date('Y-m-d', strtotime('first day of next month'));
                }

                if($stage['estimated_completion_date'] && $stage['estimated_completion_date'] != NULL && $stage['estimated_completion_date'] != ''){
                    $stages[$i]['estimated_completion_date'] = $stage['estimated_completion_date'];
                }
                else{
                    $stages[$i]['estimated_completion_date'] = date('Y-m-d', strtotime('+1 week', strtotime($stages[$i]['estimated_start_date'])) );
                    // $stages[$i]['estimated_completion_date'] = date('Y-m-d', strtotime('first day of next month'));
                }

                // check end date is AFTER start
                if($stages[$i]['scheduled_start_date'] != NULL){
                    if(strtotime($stages[$i]['estimated_completion_date']) < strtotime($stages[$i]['scheduled_start_date'])){
                        $stages[$i]['estimated_completion_date'] = $stages[$i]['scheduled_start_date'];
                    }
                }
                else{
                    if(strtotime($stages[$i]['estimated_completion_date']) < strtotime($stages[$i]['estimated_start_date'])){
                        $stages[$i]['estimated_completion_date'] = $stages[$i]['estimated_start_date'];
                    }
                }
            }
            $first_stage = $stages[0];

            $last_stage = $stages[count($stages) - 1];

            $project->start_date = (($first_stage['scheduled_start_date']) && $first_stage['scheduled_start_date'] != NULL && $first_stage['scheduled_start_date'] != '' ) ? $first_stage['scheduled_start_date'] : $first_stage['estimated_start_date'];
            $project->end_date = $last_stage['estimated_completion_date'];
        }
        else{
            $project->start_date = ($request->start_date) ? $request->start_date : $project->start_date;
            $project->end_date = ($request->end_date) ? $request->end_date : $project->end_date;
        }

        $project->stages = $stages;



        $project->schedules = $request->schedules ? $request->schedules : array();

        $project->materials = $request->materials ? $request->materials: array();
        // $project->materials = $project->schedules;

        $project->manager_id = $request->manager_id ? $request->manager_id : "";

        $project->save();

        return $project;
    }

    public function update(Request $request, Project $project){
        $this->validate($request, [
            'name' => 'required|max:255',
            'budget_note' => 'nullable|max:190',
            'client_id' => 'required|integer|exists:team_clients,id',
            'active' => 'required|integer',
            // 'start_date' => 'nullable|date',
            // 'end_date' => 'nullable|date|after_or_equal:start_date',
            'cost_materials' => 'nullable|numeric',
            'job_value' => 'nullable|numeric',
            'labor_allowance' => 'nullable|numeric',
            'contract_value' => 'nullable|numeric',
            'cost_labor' => 'nullable|numeric',
            'total_area' => 'nullable|numeric',
            'labor_budget' => 'nullable|numeric',
            'material_budget' => 'nullable|numeric',

            'live_profit' => 'nullable|numeric',
            'goal_gross_profit' => 'nullable|numeric',
            'critical_number' => 'nullable|numeric',
            'labor_critical_number' => 'nullable|numeric',
            //   'brick_count_floor_0' => 'nullable|integer',
            //   'brick_count_floor_1' => 'nullable|integer',
            //   'brick_count_floor_2' => 'nullable|integer',
            //   'brick_count_floor_3' => 'nullable|integer',
            // 'brick_type' => ['regex:/^(austral|pgh|recycled|common|other)$/'],
            // 'brick_color' => ['regex:/^(standard|common|other)$/'],
            'brick_type' => 'required|max:255',
            'brick_color' => 'required|max:255',
            'cement_color' => ['regex:/^(white|natural|oxide|other)$/'],
            'manager_id' => 'nullable|integer',

        ]);

        $project->client_id = $request->client_id;

        $original_status = $project->active;

        $project->name = $request->name;
        $project->address_line_1 = $request->address_line_1;
        $project->address_line_2 = $request->address_line_2;
        $project->address_suburb = $request->address_suburb;
        $project->address_state = $request->address_state;
        $project->address_postcode = $request->address_postcode;
        // $project->start_date = $request->start_date;
        // $project->end_date = $request->end_date;
        $project->active = $request->active;
        $project->po_number = $request->po_number;
        $project->cost_materials = $request->cost_materials;
        $project->job_value = $request->job_value;
        $project->labor_allowance = $request->labor_allowance;
        $project->contract_value = $request->contract_value;
        $project->cost_labor = $request->cost_labor;
        $project->labor_budget = $request->labor_budget;
        $project->material_budget = $request->material_budget;
        //$project->gross_profit = $request->gross_profit;
        $project->live_gross_profit = $project->contract_value - $project->wages() - $project->material_costs_total;
        $project->goal_gross_profit = $project->contract_value - $project->cost_labor - $project->material_costs;

        if(($project->contract_value - $project->cost_materials) <= 0){
            $project->critical_number = 0;
        }
        else{
            $project->critical_number = ($project->wages() / ($project->contract_value - $project->cost_materials)) * 100;
        }

        if(($project->labor_allowance - $project->wages()  ) <= 0){
            $project->labor_critical_number = 0;
        }
        else {
            $the_wages = $project->wages();
            $project->labor_critical_number = ($the_wages / ($project->labor_allowance) * 100);
        }



        // $project->total_area = $request->total_area;
        // $project->brick_count_floor_0 = $request->brick_count_floor_0;
        // $project->brick_count_floor_1 = $request->brick_count_floor_1;
        // $project->brick_count_floor_2 = $request->brick_count_floor_2;
        // $project->brick_count_floor_3 = $request->brick_count_floor_3;
        $project->cement_color = $request->cement_color;
        $project->brick_type = $request->brick_type;
        $project->brick_color = $request->brick_color;

        $project->project_notes = $request->project_notes;
        $project->budget_note = $request->budget_note;

        $project->supervisor_name = $request->supervisor_name ? $request->supervisor_name : $project->supervisor_name;
        $project->supervisor_phone = $request->supervisor_phone ? $request->supervisor_phone : $project->supervisor_phone;
        $project->supervisor_email = $request->supervisor_email ? $request->supervisor_email : $project->supervisor_email;

        $project->job_type = $request->job_type ? $request->job_type : $project->job_type;

        $stages = array();
        $stages = $request->stages;

        if(count($request->stages)){
            foreach($stages as $i => $stage){
                if($stage['estimated_start_date'] && $stage['estimated_start_date'] != NULL && $stage['estimated_start_date'] != ''){
                    $stages[$i]['estimated_start_date'] = $stage['estimated_start_date'];
                }
                else{
                    $stages[$i]['estimated_start_date'] = date('Y-m-d', strtotime('first day of next month'));
                }

                if($stage['estimated_completion_date'] && $stage['estimated_completion_date'] != NULL && $stage['estimated_completion_date'] != ''){
                    $stages[$i]['estimated_completion_date'] = $stage['estimated_completion_date'];
                }
                else{
                    $stages[$i]['estimated_completion_date'] = date('Y-m-d', strtotime('+1 week', strtotime($stages[$i]['estimated_start_date'])) );
                }

                // check end date is AFTER start
                if($stages[$i]['scheduled_start_date'] != NULL){
                    if(strtotime($stages[$i]['estimated_completion_date']) < strtotime($stages[$i]['scheduled_start_date'])){
                        $stages[$i]['estimated_completion_date'] = $stages[$i]['scheduled_start_date'];
                    }
                }
                else{
                    if(strtotime($stages[$i]['estimated_completion_date']) < strtotime($stages[$i]['estimated_start_date'])){
                        $stages[$i]['estimated_completion_date'] = $stages[$i]['estimated_start_date'];
                    }
                }

            }
            $first_stage = $stages[0];

            $last_stage = $stages[count($stages) - 1];

            $project->start_date = (($first_stage['scheduled_start_date']) && $first_stage['scheduled_start_date'] != NULL && $first_stage['scheduled_start_date'] != '' ) ? $first_stage['scheduled_start_date'] : $first_stage['estimated_start_date'];
            $project->end_date = $last_stage['estimated_completion_date'];
        }
        else{
            $project->start_date = ($request->start_date) ? $request->start_date : $project->start_date;
            $project->end_date = ($request->end_date) ? $request->end_date : $project->end_date;
        }

        // new status = 4 ( complete ) and original status was not complete or archived
        if($project->active == 4 && $original_status != 4){
            $project->date_completed = date('Y-m-d');
        }
        // if we're setting the status to anything other than active or archived
        else if($project->active != 4 && $project->active != 0){
            $project->date_completed = NULL;
        }

        $project->stages = $request->stages ? $stages : $project->stages;
        $project->schedules = $request->schedules ? $request->schedules : $project->schedules;
        $project->materials = $request->materials ? $request->materials : $project->materials;





        $project->manager_id = $request->manager_id ? $request->manager_id : null;

        $project->material_costs_total = $project->total_material_cost();
        $project->operating_percentage = $project->operating_percentage();
        $project->recalculate_cn();
        $project->recalculate_labor_cn();
        $project->save();

        return $project;
    }

    public function delete(Request $request, Project $project){
        if($request->User()->currentTeam()->pivot->role == 'owner'){
            return $project->delete();
        }
        return false;
    }

    public function update_dates(Request $request, Project $project){
        $this->validate($request, [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if(isset($request->stage_index)){
            $stage = $request->stage_index;
            $stages = $project->stages;

            $stages[$stage]['scheduled_start_date'] = $request->start_date;
            $stages[$stage]['estimated_completion_date'] = $request->end_date;

            $project->stages = $stages;
        }
        else{
            $project->start_date = $request->start_date;
            $project->end_date = $request->end_date;
        }

        $project->save();

        return $project;
    }

    public function toggle(Request $request, Project $project){
        $original_status = $project->active;

        $project->active = $request->active;

        // new status = 4 ( complete ) and original status was not complete or archived
        if($project->active == 4 && $original_status != 4){
            $project->date_completed = date('Y-m-d');
        }
        // if we're setting the status to anything other than active or archived
        else if($project->active != 4 && $project->active != 0){
            $project->date_completed = NULL;
        }


        $project->save();
        return $project;
    }
}