Vue.component('tbb-project-details',{
	props: ['project', 'staff', 'user'],

	/**
	 * The component's data.
	 */
	data() {
		return {
			user_role: 'staff',
			the_project: this.project,
			comments: [],
			project_statuses: ['Archived', 'Pending', 'Scheduled', 'In Progess', 'Completed'],
		}
	},

	beforeMount() {
		var this_user = this.$props.user;
		var userTeam = this_user.teams.filter( team => team.id == this_user.current_team_id);
		if(userTeam) {
			this.user_role = userTeam[0].pivot.role;
		}
		this.the_project = this.$props.project
	},

	computed: {


		est_start() {
			let start = this.the_project.stages[0].estimated_start_date;
			return start || 'TBA';
		},
		est_end() {
			let stagesEnd = this.the_project.stages.length - 1;
			let end = this.the_project.stages[stagesEnd].estimated_completion_date;
			return end || 'TBA';
		},
		manager_name(){
			var user_id = this.the_project.manager_id;
			for(var i=0; i<this.staff.length; i++){
				if(user_id == this.staff[i].id){
					return this.staff[i].name + ' ' + this.staff[i].surname;
				}
			}
		 },
		total_bricks(){
			var x = 0;
			if(this.the_project.stages.length){
				for(var i=0; i < this.the_project.stages.length; i++){
					x += parseInt(this.the_project.stages[i].brick_count);
				}
			}

			return x;
		},
		//material_costs_total() {

			//return this.

			//Vue.set(this.form.materials, 'item', material_items);
			//	},

		//},

		total_blocks(){
			var x = 0;
			if(this.the_project.stages.length){
				for(var i=0; i < this.the_project.stages.length; i++){
					// console.log(this.the_project.stages[i])
					x += parseInt(this.the_project.stages[i].block_count);
				}
			}

			return x;
		},
		show_schedules(){
			return this.check_schedule_display();
		},
		show_brick_schedules(){
			return this.check_brick_schedules();
		},
		show_sand_schedules(){
			return this.check_sand_schedules();
		}
	},

	created() {
		var self = this;

		Bus.$on('updateCurrentProject', function () {
			self.updateProject();
		});
	},

	watch: {

	},

	methods: {
		addCommas(num){
			if(isNaN(parseInt(num))){
				return num;
			}
			return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		},
		updateProject() {
			Bus.$emit('startRequest');
			axios.get('/projects/single/'+this.project.id)
				.catch(error => {})
				.then(response => {
					$.extend(this.the_project, response.data);

					$('#modal-update-project').before('<div class="project-update-success alert alert-success">'+this.the_project.name+' <strong>Updated</strong></div>');
					Bus.$emit('finishedRequest');
					setTimeout(function(){
						$('.project-update-success:last').fadeOut(500, function(){
							$(this).remove();
						});
					}, 5000);
				});
		},
		deleteProject(){
			form = new SparkForm();
			Bus.$emit('startRequest');
			Spark.put('/projects/'+this.the_project.id+'/delete', form)
				.catch(error => {})
				.then(response => {
					window.location = '/projects?project_deleted='+encodeURIComponent(this.the_project.name);
					Bus.$emit('finishedRequest');
				});
		},
		open_delete_modal(){
			var project = this.the_project;
			var project_id = this.the_project.id;
			var extras = [];
			var modal_id = '#modal-project-'+project_id+'-confirm-delete';

			if(project.jobs.length){ extras.push('<strong class="text-danger">'+project.jobs.length+' scheduled jobs</strong>'); }
			if(project.comments.length){ extras.push('<strong class="text-danger">'+project.comments.length+' comments</strong>'); }
			if(project.documents.length){ extras.push('<strong class="text-danger">'+project.documents.length+' documents</strong>'); }
			if(project.photos.length){ extras.push('<strong class="text-danger">'+project.photos.length+' photos</strong>'); }

			jQuery(modal_id+' .extra_content').hide();

			if(extras.length){
				jQuery(modal_id+' .extra_content span.extra_details').html( extras.join(', ') );
				jQuery(modal_id+' .extra_content').show();
			}

			jQuery(modal_id).modal('show');
		},
		check_schedule_display(){
			if(this.the_project.schedules == null){
				return false;
			}

			var x = false;
			
			if(this.the_project.schedules.hardware != null && this.the_project.schedules.hardware != ''){
				x = true;
			}
			if(this.the_project.schedules.lintel != null && this.the_project.schedules.lintel != ''){
				x = true;
			}
			if(this.the_project.schedules.wall_wrap_delivery != null && this.the_project.schedules.wall_wrap_delivery != ''){
				x = true;
			}
			if(this.the_project.schedules.wall_wrap_installation != null && this.the_project.schedules.wall_wrap_installation != ''){
				x = true;
			}

			if(this.check_brick_schedules()){
				x = true;
			}

			if(this.check_sand_schedules()){
				x = true;
			}

			return x;
		},
		check_brick_schedules(){
			if(this.the_project.schedules.bricks != null && this.the_project.schedules.bricks.length != 0){
				for(var i=0; i<this.the_project.schedules.bricks.length; i++){
					if(this.the_project.schedules.bricks[i].date != null && this.the_project.schedules.bricks[i].date != ''){
						return true;
					}
				}
			}
			return false;
		},
		check_sand_schedules(){
			if(this.the_project.schedules.sand != null && this.the_project.schedules.bricks.sand != 0){
				for(var i=0; i<this.the_project.schedules.sand.length; i++){
					if(this.the_project.schedules.sand[i].date != null && this.the_project.schedules.sand[i].date != ''){
						return true;
					}
				}
			}
			return false;
		}

	}
});
