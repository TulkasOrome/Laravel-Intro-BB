const { camelCase } = require("jquery");

Vue.component('tbb-update-project',{
	props: ['project', 'clients', 'staff'],

	/**
	 * The component's data.
	 */
	data() {
		return {
			supplied_cement: false,
			the_stages: [
				{ name: 'Pending', value: 1 },
				{ name: 'Scheduled', value: 2 },
				{ name: 'Completed', value: 4 }
			],
			brick_count: 0,
			block_count: 0,
			labor_budget: 0,
			cement_color: '',
			total_cement_cost: 0,
			total_project_value: 0,
			total_cost_labor: 0,
			budget_note: '',
			project_stages: [],
			blank_date: { date: '', quantity: '' },
			blank_project_stage: {
				name: '',
				can_be_deleted: 1,
				brick_count: 0,
				block_count: 0,
				estimated_start_date: '',
				scheduled_start_date: '',
				estimated_completion_date: '',
				stage_value: 0,
				labor_cost: 0.0,
				cement_cost: 0.0,
				net_profit: 0.0
			},
			form: $.extend(true, new SparkForm({}), this.project)
		};
	},

	mounted(){
		$.extend(this.project_stages, this.project.stages);

		var self = this;
		this.supplied_cement = this.project.schedules.supplied_cement;
		this.brick_count = this.project.brick_count;
		this.block_count = this.project.block_count;
		this.cement_color = this.project.cement_color;
		this.labor_budget = this.project.labor_budget;
		this.total_project_value = this.project.job_value;
		this.budget_note = this.project.budget_note;
		this.cost_labor = this.project.cost_labor;

		this.update_stage_data();
		// this.calc_min_stage_values();
		// this.update_stage_data();

		jQuery('#modal-update-project').on('change keyup', '.brick_count', function(){ self.update_stage_data(); });
		jQuery('#modal-update-project').on('change keyup', '.block_count', function(){ self.update_stage_data(); });
		jQuery('#modal-update-project').on('change keyup', '.stage_value', function(){ self.update_stage_data(); });
	},

	computed: {

	},

	created() {

	},

	watch: {
		supplied_cement: function(val) {
			Vue.set(this.form.schedules, 'supplied_cement', val);
			this.project.schedules.supplied_cement = val;

			if(val) {
				this.total_cement_cost = 0;
			}

			this.update_stage_data();
		},
		cement_color: function(val){
			Vue.set(this.form, 'cement_color', val);
			this.update_stage_data();
		},
		total_project_value: function(val){
			Vue.set(this.form, 'job_value', val);
		},
		total_cement_cost: function(val){
			Vue.set(this.form, 'cost_materials', val);
		},
		brick_count: function(val){
			Vue.set(this.form, 'brick_count', val);
			this.update_stage_data();
		},
		block_count: function(val){
			Vue.set(this.form, 'block_count', val);
			this.update_stage_data();
		},
		labor_budget: function(val){
			this.updateTotalCostLabor(val)
		},
		total_cost_labor: function(val){
			Vue.set(this.form, 'cost_labor', val);
			this.update_stage_data();
		},
		project_stages: function(val){
			Vue.set(this.form, 'stages', val);
		}
	},

	methods: {
		updateTotalCostLabor(val) {
			this.calc_cement_costs();
			var cost_labor = 0;
			var pc = val / 100;
			var net_value = parseFloat(this.total_project_value);
			net_value = (isNaN(net_value)) ? 0 : net_value;
			if(net_value > 0){
				var gross_budget = net_value - parseFloat(this.total_cement_cost);
				cost_labor = (gross_budget * pc).toFixed(2);
			}
			else{
				cost_labor = 0;
			}

			Vue.set(this.form, 'labor_budget', val);
			this.total_cost_labor = cost_labor;

		},
		create() {
			Bus.$emit('startRequest');
			Spark.put('/projects/'+this.project.id, this.form)
				.then(response => {
					if(this.form.successful){
						$('#modal-update-project').modal('hide');
						Bus.$emit('updateCurrentProject');
					}
					Bus.$emit('finishedRequest');
				});
		},
		get_ordinal_suffix(n){	// according to SO, this is from shoppify :)
			var s = ["th", "st", "nd", "rd"],
				v = n % 100;
			return n+(s[(v - 20) % 10] || s[v] || s[0]);
		},
		add_brick_delivery(){
			var new_date = $.extend({}, this.blank_date);
			var new_dates = $.extend([], this.form.schedules.bricks);
			new_dates.push(new_date);
			Vue.set(this.form.schedules, 'bricks', new_dates);
		},
		add_sand_delivery(){
			var new_date = $.extend({}, this.blank_date);
			var new_dates = $.extend([], this.form.schedules.sand);
			new_dates.push(new_date);
			Vue.set(this.form.schedules, 'sand', new_dates);
		},
		remove_brick_delivery(index){
			var new_dates = $.extend([], this.form.schedules.bricks);
			new_dates.splice(index, 1);
			Vue.set(this.form.schedules, 'bricks', new_dates);
			return false;
		},
		remove_sand_delivery(index){
			var new_dates = $.extend([], this.form.schedules.sand);
			new_dates.splice(index, 1);
			Vue.set(this.form.schedules, 'sand', new_dates);
			return false;
		},
		add_project_stage(){
			var new_stage = $.extend({}, this.blank_project_stage);
			this.project_stages.push(new_stage);
			Vue.set(this.form, 'stages', this.project_stages);
			this.update_stage_data();
			return false;
		},
		remove_project_stage(index){
			if(this.project_stages[index].can_be_deleted){
				this.project_stages.splice(index, 1);
				Vue.set(this.form, 'stages', this.project_stages);
				this.update_stage_data();
			}
			return false;
		},
		reset_project_stages(){
			this.project_stages = [];
			$.extend(this.project_stages, this.project.stages);
		},
		reset_brick_deliveries(){
			this.form.schedules.bricks = [];
			$.extend(this.form.schedules.bricks, this.project.schedules.bricks);
		},
		reset_sand_deliveries(){
			this.form.schedules.sand = [];
			$.extend(this.form.schedules.sand, this.project.schedules.sand);
		},
		recommended_values(){
			this.suggested_stage_values();
		},
		calc_cement_costs(){
			// ADD IN CHECKBOX ' MORTAR SUPPLIED' if supplied return 0;
			var factor = (this.cement_color == 'natural') ? 0.07 : 0.10;
			var block_factor = (this.cement_color == 'natural') ? 0.14 : 0.20;
			var supplied = this.supplied_cement;;



			// console.log('this.block_count')
			// console.log(this.block_count)
			// console.log('this.brick_count')
			// console.log(this.brick_count)

			var brick_costs = (this.brick_count * factor).toFixed(2);
			var block_costs = (this.block_count * block_factor).toFixed(2);
			var total_costs = parseInt(brick_costs) + parseInt(block_costs);

			if (supplied) {
				this.total_cement_cost = 0;
				for(var i=0; i<this.project_stages.length; i++){
					this.project_stages[i].cement_cost = 0;
				}

			} else {
				this.total_cement_cost = (!isNaN(total_costs) ? total_costs : 0);

				for(var i=0; i<this.project_stages.length; i++){
					var stageBrickCosts = (factor * parseInt(this.project_stages[i].brick_count)).toFixed(2);
					var stageBlockCosts= (block_factor * parseInt(this.project_stages[i].block_count)).toFixed(2);
					stageBrickCosts = (isNaN(stageBrickCosts)) ? 0 : stageBrickCosts;
					stageBlockCosts = (isNaN(stageBlockCosts)) ? 0 : stageBlockCosts;
					this.project_stages[i].cement_cost = parseInt(stageBrickCosts) + parseInt(stageBlockCosts);
				}

			}

		},
		update_brick_counts(){
			var brick_count = 0;
			for(var i=0; i<this.project_stages.length; i++){
				brick_count += (isNaN(parseInt(this.project_stages[i].brick_count))) ? 0 : parseInt(this.project_stages[i].brick_count);
			}
			this.brick_count = brick_count;
			this.form.brick_count = brick_count;
		},
		update_block_counts(){
			var block_count = 0;
			for(var i=0; i<this.project_stages.length; i++){
				block_count += (isNaN(parseInt(this.project_stages[i].block_count))) ? 0 : parseInt(this.project_stages[i].block_count);
			}
			this.block_count = block_count;
			this.form.block_count = block_count;
		},
		calc_min_stage_values(){
			var cement = 0;
			var value = 0;

			for(var i=0; i<this.project_stages.length; i++){
				value = parseFloat(this.project_stages[i].stage_value);
				cement = parseFloat(this.project_stages[i].cement_cost);

				if(isNaN(value) || value < cement){
					this.project_stages[i].stage_value = cement.toFixed(2);
				}
			}
		},
		suggested_stage_values(){
			var pc = 0;
			var sbricks = 0;
			var bricks = parseInt(this.brick_count);
			var total_value = parseFloat(this.total_project_value);

			for(var i=0; i<this.project_stages.length; i++){

				sbricks = parseInt(this.project_stages[i].brick_count);

				sbricks = (isNaN(sbricks)) ? 0 : sbricks;
				pc = sbricks / bricks;

				if(sbricks <= 0){
					this.project_stages[i].stage_value = 0;
					continue;
				}

				this.project_stages[i].stage_value = (pc * total_value).toFixed(2);
			}

			this.update_stage_data();
		},
		calc_labor_budgets(){
			var cost_labor = 0;
			var net_value = 0;
			var labor_budget = parseFloat(this.labor_budget);
			this.updateTotalCostLabor(labor_budget);

			var pc = (isNaN(labor_budget) || labor_budget == 0) ? 0 : labor_budget / 100;

			for(var i=0; i<this.project_stages.length; i++){
				net_value = parseFloat(this.project_stages[i].stage_value);
				net_value = (isNaN(net_value) || net_value < 0) ? 0 : net_value;

				if(net_value > 0){
					gross_budget = net_value - parseFloat(this.project_stages[i].cement_cost);
					cost_labor = (gross_budget * pc).toFixed(2);
					net_profit = (gross_budget - cost_labor).toFixed(2);
				}
				else{
					cost_labor = 0;
					net_profit = 0;
				}

				this.project_stages[i].labor_cost = cost_labor;
				this.project_stages[i].net_profit = net_profit;
			}
		},
		update_stage_data(){
			this.update_brick_counts();
			this.update_block_counts();
			this.calc_cement_costs();
			this.calc_labor_budgets();

			var stage = {};
			var new_stages = [];

			for(var j=0; j<this.project_stages.length; j++){
				stage = {};
				$.extend(stage, this.project_stages[j]);
				new_stages.push(stage);
			}

			this.project_stages = [];
			this.project_stages = new_stages;
		}
	}
});
