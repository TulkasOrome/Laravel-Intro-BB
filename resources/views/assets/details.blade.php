<tbb-project-details :user="user" :project="currentProject" :staff="currentTeam.users" inline-template>
	<div class="">
		<div class="tbb-project-details-primary card card-default tbb-card">
			<div class="card-header tbb-card__header">
				<div class="tbb-card__header-title"><strong>@{{ the_project.name }}</strong><span> - @{{ project_statuses[the_project.active] }}</span></div>
				@if (Auth::user()->hasTeams() && Auth::user()->ownsTeam(Auth::user()->currentTeam))
					<div class="tbb-card__header-actions">
						<div class="btn-group tbb-btn-group">
							<button type="button" class="btn btn-danger btn-sm float-right ml-2" v-on:click="open_delete_modal"><i class="fa fa-trash"></i></button>
							<button type="button" class="btn btn-warning btn-sm float-right text-white" data-toggle="modal" data-target="#modal-update-project"><i class="fa fa-pencil"></i></button>
						</div>
					</div>
				@endif
			</div>
			<div class="card-body tbb-card__body">
				<div class="row">
					<div class="col-12 col-lg-6 mb-4">
						<h6><strong>Basic Details</strong></h6>

						<!-- Client -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Client')}}</div>
							<div class="col-6 col-md-5">
								<span><a :href="'/clients/'+the_project.client_id">@{{ the_project.client[0].name }}</a></span>
							</div>
						</div>
						<!-- PO Number -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('PO Number')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ the_project.po_number }}</span>
							</div>
						</div>

						<!-- Manager -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Manager')}}</div>
							<div class="col-6 col-md-5">
								<a v-if="the_project.manager_id" href="/settings/teams/1#/membership">@{{ manager_name }}</a>
								<span v-else>@{{ manager_name }}</span>
							</div>
						</div>
						<!-- Address Line 1 -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">Address</div>
							<div class="col-6 col-md-5">
								<span>@{{ the_project.address_line_1 }}</span>
								<span v-if="the_project.address_line_2 != '' && the_project.address_line_2 != null"><br>@{{ the_project.address_line_2 }}</span>
								<span><br>@{{ the_project.address_suburb }}</span>
								<span><br>@{{ the_project.address_state }}</span>, <span>@{{ the_project.address_postcode }}</span>
							</div>
						</div>

						<!-- Project Type -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Type')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ the_project.job_type }}</span>
							</div>
						</div>

						<!-- Notes -->

						<hr />
						<div class="row" v-if="(the_project.project_notes != '' && the_project.project_notes != null)">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Notes')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ the_project.project_notes }}</span>
							</div>
						</div>

						<h6 class="mt-4"><strong>Contact Information</strong></h6>
						<!-- Supervisor Name -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Supervisor Name')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ the_project.supervisor_name }}</span>
							</div>
						</div>
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Supervisor Phone')}}</div>
							<div class="col-6 col-md-5">
								<a v-if=" the_project.supervisor_phone != '' " :href="'tel:'+the_project.supervisor_phone">@{{ the_project.supervisor_phone }}</a>
								<span v-else >@{{ the_project.supervisor_phone }}</span>
							</div>
						</div>
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Supervisor Email')}}</div>
							<div class="col-6 col-md-5">
								<a v-if=" the_project.supervisor_email != '' " :href="'mailto:'+the_project.supervisor_email">@{{ the_project.supervisor_email }}</a>
								<span v-else >@{{ the_project.supervisor_email }}</span>
							</div>
						</div>

					</div>
					<div class="col-12 col-lg-6 mb-4">
						<h6><strong>Schedule</strong></h6>
						<!-- Start Date -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Estimated Start Date')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ est_start }}</span>
							</div>
						</div>
						<!-- end_date -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Estimated End Date')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ est_end }}</span>
							</div>
						</div>
						<hr />
						<h6 class="mt-4"><strong>Budgets</strong></h6>
						<!-- Total Value -->
						<div class="row" v-if="user_role === 'owner'">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Project Value')}}</div>
							<div class="col-6 col-md-5">
								<span>$@{{ (the_project.job_value) ? the_project.job_value.toLocaleString() : 0.00 }}</span>
							</div>
						</div>

						<!-- Cement Cost -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Cement Cost')}}</div>
							<div class="col-6 col-md-5">
								<span>$@{{ (the_project.cost_materials && !the_project.schedules.supplied_cement) ? the_project.cost_materials.toLocaleString() : 0.00 }}</span>
							</div>
						</div>

						<!-- Target Labour Budget -->
						<div class="row">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Goal Labour Budget')}}</div>
							<div class="col-6 col-md-5" v-if="user_role === 'owner'">
								<span>@{{ the_project.labor_budget }}%</span>
							</div>
						</div>

						<!-- Target Labour Cost -->
						<div class="row" v-if="user_role === 'owner'">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Goal Labour Cost')}}</div>
							<div class="col-6 col-md-5">
								<span>$@{{ the_project.cost_labor.toLocaleString() || 0 }}</span>
							</div>
						</div>

						<!-- Goal Gross Profit -->
						<div class="row" v-if="user_role === 'owner'">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Goal Gross Profit')}}</div>
							<div class="col-6 col-md-5">
								<span>$@{{ the_project.goal_gross_profit.toLocaleString() || 0 }}</span>
							</div>
						</div>

						<!-- Live Wages -->
						<div class="row" v-if="user_role === 'owner'">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Live Labour Cost')}}</div>
							<div class="col-6 col-md-5">
								<span>$@{{ the_project.wages.toLocaleString() || 0 }}</span>
							</div>
						</div>

						<!-- Live Gross Profit -->
						<div class="row" v-if="user_role === 'owner'">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Live Gross Profit')}}</div>
							<div class="col-6 col-md-5">
								<span>$@{{ the_project.live_gross_profit.toLocaleString() || 0 }}</span>
							</div>
						</div>

						<!-- Critical Number -->
						<div class="row" v-if="user_role === 'owner'">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Critical Number')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ (the_project.critical_number) ? Math.floor(the_project.critical_number) : 0 }}%  (CN)</span>
							</div>
						</div>

						<hr v-if="the_project.budget_note && user_role === 'owner'" />
						<!-- Notes -->
						<div class="row" v-if="the_project.budget_note  && user_role === 'owner'">
							<div class="col-6 col-md-5 col-detail-label text-lg-right">{{__('Budget Note')}}</div>
							<div class="col-6 col-md-5">
								<span>@{{ the_project.budget_note }}</span>
							</div>
						</div>

					</div>
				</div>
			</div><!-- /.card-body tbb-card__body -->


			@if (Auth::user()->hasTeams() && Auth::user()->ownsTeam(Auth::user()->currentTeam))
				<div class="modal fade" :id="'modal-project-'+project.id+'-confirm-delete'" tabindex="-1" role="dialog" data-keyboard="false">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<div class="modal-body">
								<div class="container-fluid text-center">
									<h3 class="mt-2"><i class="fa fa-warning"></i></h3>
									<div class="mb-4">
										Are you sure you want to delete the project<br />
										<strong>@{{ project.name }}</strong>?
									</div>

									<div class="mb-4 extra_content">
										This project already has <span class="extra_details"></span> attached to it already.<br><br>
										If you delete this project, <strong>all of this data will be deleted forever</strong>.
									</div>

									<div class="mb-2">
										<a href="#" class="btn btn btn-primary btn-lg" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">Cancel</span>
										</a>
										<a v-on:click="deleteProject" class="btn btn btn-danger btn-lg text-white" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">Delete</span>
										</a>
									</div>
								</div><!-- /.container-fluid -->
							</div><!-- /.modal-body -->
						</div>
					</div>
				</div>
			@endif
		</div>

		<div class="tbb-project-materials card card-default tbb-card">
			<div class="card-header tbb-card__header">
				<div class="tbb-card__header-title">Materials</div>
			</div>
			<div class="card-body tbb-card__body">
				<div class="row">
					<div class="col-12 col-lg-6 mb-2">
						<h6><strong>Material Details</strong></h6>

						<!-- Masonry Manufacturer -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Masonry Manufacturer')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.brick_color }}</span>
							</div>
						</div>

						<!-- Unit Type -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Unit Type')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.brick_type }}</span>
							</div>
						</div>


						<!-- Unit Type -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Total Bricks')}}</div>
							<div class="col-6 col-md-6">
								<span class="text-capitalize">@{{ (total_bricks) ? total_bricks.toLocaleString() : 0 }}</span>
							</div>
						</div>


						<!-- Unit Type -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Total Blocks')}}</div>
							<div class="col-6 col-md-6">
								<span class="text-capitalize">@{{ (total_blocks) ? total_blocks.toLocaleString() : 0 }}</span>
							</div>
						</div>

						<!-- Unit Type -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Mortar Color')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.cement_color }}</span>
							</div>
						</div>

						<!-- Unit Type -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Joint Finish')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.joint_finish }}</span>
							</div>
						</div>
					</div>
					&nbsp;
					<div class="col-12 col-lg-6 mb-2">
						<h6><strong>Delivery Schedules</strong></h6>

						<!-- Lintel Delivery -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Lintel Delivery')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.lintel || 'Not Scheduled' }}</span>
							</div>
						</div>

						<!-- Hardware Delivery -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Hardware Delivery')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.hardware || 'Not Scheduled' }}</span>
							</div>
						</div>

						<!-- Scaffold Erection -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Scaffold Erection Date')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.scaffold || 'Not Scheduled' }}</span>
							</div>
						</div>

						<!-- Wall Wrap Delivery -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Wall Wrap Delivery')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.wall_wrap_delivery || 'Not Scheduled' }}</span>
							</div>
						</div>

						<!-- Wall Wrap Installation -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Wall Wrap Installation')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.wall_wrap_installation || 'Not Scheduled' }}</span>
							</div>
						</div>

						<!-- Wall Wrap Installation -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Bushfire WEEPAS')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.weepas || 'Not Required' }}</span>
							</div>
						</div>

						<!-- Supplied Cement -->
						<div class="row">
							<div class="col-6 col-md-6 col-detail-label text-lg-right">{{__('Builder Supplying Cement')}}</div>
							<div class="col-6 col-md-6">
								<span>@{{ the_project.schedules.supplied_cement || 'False' }}</span>
							</div>
						</div>


					</div>
				</div>
				&nbsp;

				<div class="row">
					<div class="col-12 col-lg-6 mb-2">
						<h6><strong> Brick Deliveries</strong></h6>
						<template v-if="the_project.schedules.bricks.length && the_project.schedules.bricks[0].date !== null">
							<div class="col-6 col-md-6" v-for="bricks in the_project.schedules.bricks">
								<div class="row">
									<div class="col-6 col-md-6 col-detail-label text-lg-right"><strong>@{{ bricks.date }}</strong></div>
									<div class="col-6 col-md-6">
										<span>@{{ addCommas(bricks.quantity) }}</span>
									</div>
								</div>
							</div>
						</template>
						<div v-else>
							Not Scheduled
						</div>

					</div>
					<div class="col-12 col-lg-6 mb-2">
						<h6><strong> Sand Deliveries</strong></h6>
						<template v-if="the_project.schedules.sand.length && the_project.schedules.sand[0].date !== null">
							<div class="col-6 col-md-6" v-for="sand in the_project.schedules.sand">
								<div class="row">
									<div class="col-6 col-md-6 col-detail-label text-lg-right"><strong>@{{ sand.date }}</strong></div>
									<div class="col-6 col-md-6">
										<span>@{{ addCommas(sand.quantity) }}</span>
									</div>
								</div>
							</div>
						</template>
						</template>
						<div v-else>
							Not Scheduled
						</div>

					</div>
				</div>

			</div><!-- /.card-body tbb-card__body -->
		</div>
	@include('project.comments')

	<!-- STAGES -->

		<div class="tbb-project-stages card card-default tbb-card">
			<div class="card-header tbb-card__header">
				<div class="tbb-card__header-title">Stages</div>
			</div>
			<div class="card-body tbb-card__body">
				<div class="row">
					<div class="tbb-project-stage" v-for="(stage, index) in the_project.stages" :data-stage-number="index">
						<div class="tbb-project-stage__header">
							<div class="tbb-project-stage__name">@{{ stage.name }}</div>
						</div>
						<div class="tbb-project-stage__data">
							<div class="tbb-project-stage__data-title">Brick Count</div>
							<div class="tbb-project-stage__data-value">
								<span>@{{ (stage.brick_count) ? stage.brick_count.toLocaleString() : 0 }}</span>
							</div>
						</div>
						<div class="tbb-project-stage__data">
							<div class="tbb-project-stage__data-title">Block Count</div>
							<div class="tbb-project-stage__data-value">
								<span>@{{ (stage.block_count) ? stage.block_count.toLocaleString(2) : 0 }}</span>
							</div>
						</div>
						<div class="tbb-project-stage__data">
							<div class="tbb-project-stage__data-title">Estimated Start Date</div>
							<div class="tbb-project-stage__data-value">
								<span>@{{ (stage.estimated_start_date) ? stage.estimated_start_date : '-' }}</span>
							</div>
						</div>
						<div class="tbb-project-stage__data">
							<div class="tbb-project-stage__data-title">Estimated Completion Date</div>
							<div class="tbb-project-stage__data-value">
								<span>@{{ (stage.estimated_completion_date) ? stage.estimated_completion_date : '-' }}</span>
							</div>
						</div>


						<div class="tbb-project-stage__data" v-if="user_role === 'owner'">
							<div class="tbb-project-stage__data-title">Stage Value</div>
							<div class="tbb-project-stage__data-value">
								<span>$@{{ (stage.stage_value) ? stage.stage_value.toLocaleString(2) : 0.00 }}</span>
							</div>
						</div>

						<div class="tbb-project-stage__data">
							<div class="tbb-project-stage__data-title">Cement Cost</div>
							<div class="tbb-project-stage__data-value">
								<span>$@{{ (stage.cement_cost && !the_project.schedules.supplied_cement) ? stage.cement_cost.toLocaleString(2) : 0.00 }}</span>
							</div>
						</div>

						<div v-if="user_role === 'owner' || user_role === 'manager'" class="tbb-project-stage__data">
							<div class="tbb-project-stage__data-title">Labor Budget</div>
							<div class="tbb-project-stage__data-value">
								<span>$@{{ (stage.labor_cost) ? stage.labor_cost.toLocaleString(2) : 0.00 }}</span>
							</div>
						</div>

						<div class="tbb-project-stage__data" v-if="user_role === 'owner'">
							<div class="tbb-project-stage__data-title">Gross Profit</div>
							<div class="tbb-project-stage__data-value">
								<span>$@{{ (stage.net_profit) ? stage.net_profit.toLocaleString(2) : 0.00 }}</span>
							</div>
						</div>
						<?php /*
						<div class="tbb-project-stage__data" v-if="user_role === 'owner'">
							<div class="tbb-project-stage__data-title">Live Labour Cost</div>
							<div class="tbb-project-stage__data-value">
								<span>$@{{ (the_project.all_stage_wages[index]) ? the_project.all_stage_wages[index].toLocaleString(2) : 0.00 }}</span>
							</div>
						</div>
*/ ?>

					</div>
				</div>
			</div>
		</div>

	</div>
</tbb-project-details>
