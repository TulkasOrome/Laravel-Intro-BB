<?php


namespace App;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class Project extends Model
{
    protected $table = 'team_projects';
    protected $fillable = [];
    protected $hidden = [];

    protected $casts = [
        'id' => 'integer',
        'team_id' => 'integer',
        'client_id' => 'integer',
        'manager_id' => 'integer',
        'active' => 'integer',
        // 'brick_count_floor_0' => 'integer',
        // 'brick_count_floor_1' => 'integer',
        // 'brick_count_floor_2' => 'integer',
        // 'brick_count_floor_3' => 'integer',
        'stages' => 'json',
        'schedules' => 'json',
        'materials' => 'json',
        'all_stage_wages' => []
    ];

    public static function boot(){
        parent::boot();

        self::deleting(function($project){


            $team = $project->team()->get()->first();

            // Delete all jobs
            $jobs = $project->jobs()->get();
            if(count($jobs)){
                foreach($jobs as $job){
                    $job->delete();
                }
            }

            // Detach & Delete all Documents
            $documents = $project->documents()->get();
            $project->documents()->detach();
            if(count($documents)){
                foreach($documents as $documents){
                    $documents->delete();
                }
            }

            // Delete empty project folder
            Storage::disk('public')->delete('teams/'.$team->id.'/projects/'.$project->id);

            return true;
        });
    }

    /**
     * Get the team that owns the invitation.
     */
    public function team(){
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function client(){
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function manager(){
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function jobs(){
        return $this->hasMany(Job::class);
    }

    public function documents(){
        return $this->belongsToMany(Document::class, 'project_documents', 'project_id', 'document_id');
    }

    public function comments(){
        return $this->hasMany(Comment::Class);
    }

    public function timesheets(){
        return $this->hasMany(Timesheet::class, 'project_id', 'id');
    }

    public function timesheets_by_stage(){
        $stages = array();

        $timesheets = $this->timesheets()->get();

        foreach($timesheets as $timesheet){
            $index = $timesheet->job->stage;

            if(!isset($stages[$index])){
                $stages[$index] = array();
            }

            $stages[$index][] = $timesheet;
        }

        return $stages;
    }

    public function is_booked_for_date($date){
        return count($this->jobs()->whereDate('start_date', '=', $date)->get());
    }

    public function wages(){
        $timesheets = $this->timesheets()->get();

        $total = 0.0;
        foreach($timesheets as $timesheet){
            $total += $timesheet->total;
        }

        return $total;
    }

    public function stage_wages($stage){
        $jobs = $this->jobs()->where('stage', intval($stage))->get();

        $total = 0.0;
        if ($jobs) {
            foreach ($jobs as $job) {
                $total += $job->wages();
            }
        }

        return $total;
    }

    public function all_stage_wages(){
        $stages = array();

        for ($i = 0; $i <= 10; $i++) {
            $wages = $this->stage_wages($i);
            if ($wages) {
                $stages[$i] = $this->stage_wages($i);
            }
        }

        return $stages;
    }

    // public function recalculate_stage_wages($stage){
    // 	$jobs = $this->jobs()->where('stage', intval($stage))->get();

    // 	$total = 0.0;
    // 	foreach($jobs as $job){
    // 		$total += $job->wages();
    // 	}

    // 	return $total;
    // }
    public function recalculate_cn(){
        $this->live_gross_profit = $this->contract_value - $this->wages() - $this->cost_materials;
        $this->goal_gross_profit = $this->contract_value - $this->cost_labor - $this->cost_materials;

        if(($this->contract_value - $this->material_costs_total ) <= 0){
            $this->critical_number = 0;
        }
        else {
            $the_wages = $this->wages();
            $this->critical_number = ($the_wages / ($this->contract_value - $this->material_costs_total)) * 100;
        }

        $this->save();
    }

    public function recalculate_labor_cn(){
        // $this->live_gross_profit = $this->contract_value - $this->wages() - $this->material_costs_total;
        // $this->goal_gross_profit = $this->contract_value - $this->cost_labor - $this->materiaL_costs_total;

        if(($this->labor_allowance - $this->wages()  ) <= 0){
            $this->labor_critical_number = 0;
        }
        else {
            $the_wages = $this->wages();
            $this->labor_critical_number = ($the_wages / ($this->labor_allowance) * 100);
        }

        $this->save();
    }

    public function operating_percentage(){
        $the_wages = $this->wages();
        if ( $this->contract_value == 0){
            $total = 0;
            $this->operating_percentage = $total;
            return $total;

        }
        else{
            $total = 0;
            $the_wages = $this->wages();
            $total = ($the_wages + $this->material_costs_total) / $this->contract_value * 100;
            $this->operating_percentage = $total;
            if ($total <= 0){
                $total = 0;
                return $total;
            }
            else {

                return $total;
            }

        }

    }

    public function total_material_cost(){
        if (!empty($this->materials['item'][0]['value'])) {
            $total = 0;
            $materials = $this->materials;
            foreach ($materials['item'] as $item) {
                $total += $item['value'];
            };

            $this->material_costs_total = $total;
            return $total;
        }

        else {
            $total = 0;
            $this->material_costs_total = $total;
            return $total;
        }

    }


    public function toArray(){
        $array = parent::toArray();

        $request = request();

        if($request->attach_documents){
            $documents = $this->documents()->where('document_type', 'document')->get();
            $array['documents'] = ($documents) ? $documents : [];

            $images = $this->documents()->where('document_type', 'photo')->get();
            $array['images'] = ($images) ? $images : [];
        }

        if($request->attach_client){
            $client = $this->client()->first();
            $array['client'] = ($client) ? $client : false;
        }

        if($request->attach_manager){
            // $manager = $this->manager()->first();
            $manager = $this->team()->first()->member($this->manager_id)->first();
            $array['manager'] = ($manager) ? $manager : false;
        }

        if($request->attach_team){
            $team = $this->team()->first();
            $array['team'] = ($team) ? $team : false;
        }

        if(isset($request->comment_group)){
            if($request->comment_group == 'project'){
                $comments = $this->comments()->get();
                $array['comments'] = ($comments) ? $comments : [];
            }
        }
        else{
            $comments = $this->comments()->get();
            $array['comments'] = ($comments) ? $comments : [];
        }

        if(isset($request->attach_wages)){
            $array['wages'] = $this->wages();
        }
        // $array['all_stage_wages'] = $this->all_stage_wages();
        $array['all_stage_wages'] = [];

        return $array;
    }

}