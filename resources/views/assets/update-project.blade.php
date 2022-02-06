<tbb-update-project :project="currentProject" :clients="currentTeam.clients" :staff="currentTeam.users" inline-template >
<div class="modal fade" id="modal-update-project" tabindex="-1" role="dialog" data-keyboard="false">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Edit {{ $project->name }}</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="tbb-modal__tab-container">
				<ul class="nav nav-tabs" id="editTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link active" id="basic-info-tab" data-toggle="tab" href="#basic-info" role="tab" aria-controls="basic-info" aria-selected="true">Project Basics</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" id="stages-tab" data-toggle="tab" href="#stages" role="tab" aria-controls="stages" aria-selected="false">Stages</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" id="material-details-tab" data-toggle="tab" href="#material-details" role="tab" aria-controls="material-details" aria-selected="false">Materials</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" id="pricing-details-tab" data-toggle="tab" href="#pricing-details" role="tab" aria-controls="pricing-details" aria-selected="false">Budgets &amp; Pricing</a>
					</li>
				</ul>
			</div>
			<form role="form">
				<div class="tbb-modal__body">	
						
					<div class="tab-content" id="editTabContent">
						<div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-info-tab">
								@include('project.assets.edit.details')
						</div>
						<div class="tab-pane fade show" id="stages" role="tabpanel" aria-labelledby="stages-tab">
								@include('project.assets.edit.stages')
						</div>
						<div class="tab-pane fade show" id="material-details" role="tabpanel" aria-labelledby="material-details-tab">
								@include('project.assets.edit.materials')

								@include('project.assets.edit.schedules')
						</div>
						<div class="tab-pane fade show" id="pricing-details" role="tabpanel" aria-labelledby="pricing-tab">
							@include('project.assets.edit.pricing')
						</div>
						
					</div>

				</div><!-- /.modal-body -->
				<div class="modal-footer">
					<!-- Update Button -->
					<button type="submit" class="btn btn-primary" @click.prevent="create" :disabled="form.busy">{{__('Update')}}</button>
					<!-- Close Button -->
					<a href="#" class="btn btn btn-danger" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">Cancel</span></a>
				</div>
			</form>
		</div>
	</div>
</div>
</tbb-update-project>
