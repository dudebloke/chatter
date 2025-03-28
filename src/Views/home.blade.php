@extends(Config::get('chatter.master_file_extend'))

@section(Config::get('chatter.yields.head'))
    <link href="/vendor/devdojo/chatter/assets/vendor/spectrum/spectrum.css" rel="stylesheet">
	<link href="/vendor/devdojo/chatter/assets/css/chatter.css" rel="stylesheet">
@stop

@section('content')

<div id="chatter" class="chatter_home">

	<div id="chatter_hero">
		<div id="chatter_hero_dimmer"></div>
		<h1>{{ Config::get('chatter.headline') }}</h1>
		<p>{{ Config::get('chatter.description') }}</p>
	</div>

	@if(Session::has('chatter_alert'))
		<div class="chatter-alert alert alert-{{ Session::get('chatter_alert_type') }}">
			<div class="container">
	        	<strong><i class="chatter-alert-{{ Session::get('chatter_alert_type') }}"></i> {{ Config::get('chatter.alert_messages.' . Session::get('chatter_alert_type')) }}</strong>
	        	{{ Session::get('chatter_alert') }}
	        	<i class="chatter-close"></i>
	        </div>
	    </div>
	    <div class="chatter-alert-spacer"></div>
	@endif

	@if (count($errors) > 0)
	    <div class="chatter-alert alert alert-danger">
	    	<div class="container">
	    		<p><strong><i class="chatter-alert-danger"></i> {{ Config::get('chatter.alert_messages.danger') }}</strong> Please fix the following errors:</p>
		        <ul>
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
	    </div>
	@endif

	<div class="container chatter_container">
		
	    <div class="row">

	    	<div class="col-md-3 left-column">
	    		<!-- SIDEBAR -->
	    		<div class="chatter_sidebar">
					<button class="btn btn-primary" id="new_discussion_btn"><i class="chatter-new"></i> New {{ Config::get('chatter.titles.discussion') }}</button> 
					<a href="/{{ Config::get('chatter.routes.home') }}"><i class="chatter-bubble"></i> All {{ Config::get('chatter.titles.discussion') }}</a>
					<ul class="nav nav-pills nav-stacked">
						<?php $categories = DevDojo\Chatter\Models\Category::all(); ?>
						@foreach($categories as $category)
							<li><a href="/{{ Config::get('chatter.routes.home') }}/{{ Config::get('chatter.routes.category') }}/{{ $category->slug }}"><div class="chatter-box" style="background-color:{{ $category->color }}"></div> {{ $category->name }}</a></li>
						@endforeach
					</ul>
				</div>
				<!-- END SIDEBAR -->
	    	</div>
	        <div class="col-md-9 right-column">
		        @if(count($discussions) < 1)
			        <p>Welp, guess there aren't any posts here!</p>
		        @else
		            <div class="panel">
			            <ul class="discussions">

			                @foreach($discussions as $discussion)
					            <li>
					                <a class="discussion_list" href="/{{ Config::get('chatter.routes.home') }}/{{ Config::get('chatter.routes.discussion') }}/{{ $discussion->category->slug }}/{{ $discussion->slug }}">
						                <div class="chatter_avatar">
						                    @if(Config::get('chatter.user.avatar_image_database_field'))

						                        <?php $db_field = Config::get('chatter.user.avatar_image_database_field'); ?>

						                        <!-- If the user db field contains http:// or https:// we don't need to use the relative path to the image assets -->
						                        @if( (substr($discussion->user->{$db_field}, 0, 7) == 'http://') || (substr($discussion->user->{$db_field}, 0, 8) == 'https://') )
						                            <img src="{{ $discussion->user->{$db_field}  }}">
						                        @else
						                            <img src="{{ Config::get('chatter.user.relative_url_to_image_assets') . $discussion->user->{$db_field}  }}">
						                        @endif

						                    @else

						                        <span>
						                            <img src="{{ url('/user/getthumb', $discussion->user->id) }}" class="img-responsive" style="width:40px;height:60px">
						                        </span>

						                    @endif
						                </div>

						                <div class="chatter_middle" style="margin-left:60px">
						                    <h3 class="chatter_middle_title">{{ $discussion->title }} <div class="chatter_cat" style="background-color:{{ $discussion->category->color }}">{{ $discussion->category->name }}</div></h3>
						                    <span class="chatter_middle_details">Posted By: <span data-href="/user">{{ ucfirst($discussion->user->{Config::get('chatter.user.database_field_with_user_name')}) }}</span> {{ \Carbon\Carbon::createFromTimeStamp(strtotime($discussion->created_at))->diffForHumans() }}</span>
						                    <p>{{ substr(strip_tags($discussion->post[0]->body), 0, 200) }}@if(strlen(strip_tags($discussion->post[0]->body)) > 200){{ '...' }}@endif</p>
						                </div>

						                <div class="chatter_right">

						                    <div class="chatter_count"><i class="chatter-bubble"></i> {{ $discussion->postsCount[0]->total }}</div>
						                </div>

						                <div class="chatter_clear"></div>
						            </a>
					            </li>
				            @endforeach
			            </ul>
		            </div>
		        @endif

	        	<div id="pagination">
	        		{{ $discussions->links() }}
	        	</div>

	        </div>
	    </div>
	</div>

	<div id="new_discussion">
	        	

    	<div class="chatter_loader dark" id="new_discussion_loader">
		    <div></div>
		</div>

    	<form id="chatter_form_editor" action="/{{ Config::get('chatter.routes.home') }}/{{ Config::get('chatter.routes.discussion') }}" method="POST">
        	<div class="row">
	        	<div class="col-md-7">
		        	<!-- TITLE -->
	                <input type="text" class="form-control" id="title" name="title" placeholder="Title of {{ Config::get('chatter.titles.discussion') }}" v-model="title" value="{{ old('title') }}" >
	            </div>

	            <div class="col-md-4">
		            <!-- CATEGORY -->
			            <select id="chatter_category_id" class="form-control" style="padding:0px 30px" name="chatter_category_id">
			            	<option value="">Select a Category</option>
				            @foreach($categories as $category)
				            	@if(old('chatter_category_id') == $category->id)
				            		<option value="{{ $category->id }}" selected>{{ $category->name }}</option>
				            	@else
				            		<option value="{{ $category->id }}">{{ $category->name }}</option>
				            	@endif
				            @endforeach
			            </select>
		        </div>

		        <div class="col-md-1">
		        	<i class="chatter-close"></i>
		        </div>	
	        </div><!-- .row -->

            <!-- BODY -->
        	<div id="editor">
				<label id="tinymce_placeholder">Add the content for your Discussion here</label>
    			<textarea id="body" class="richText" name="body" placeholder="">{{ old('body') }}</textarea>
    		</div>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div id="new_discussion_footer">
            	<input type='text' id="color" name="color" /><span class="select_color_text">Select a Color for this Discussion (optional)</span>
            	<button id="submit_discussion" class="btn btn-success pull-right"><i class="chatter-new"></i> Create {{ Config::get('chatter.titles.discussion') }}</button>
            	<a href="/{{ Config::get('chatter.routes.home') }}" class="btn btn-default pull-right" id="cancel_discussion">Cancel</a>
            	<div style="clear:both"></div>
            </div>
        </form>

    </div><!-- #new_discussion -->

</div>

<input type="hidden" id="chatter_tinymce_toolbar" value="{{ Config::get('chatter.tinymce.toolbar') }}">
<input type="hidden" id="chatter_tinymce_plugins" value="{{ Config::get('chatter.tinymce.plugins') }}">

@endsection

@section(Config::get('chatter.yields.footer'))



<script src="/vendor/devdojo/chatter/assets/vendor/tinymce/tinymce.min.js"></script>
<script src="/vendor/devdojo/chatter/assets/js/tinymce.js"></script>
<script src="/vendor/devdojo/chatter/assets/vendor/spectrum/spectrum.js"></script>
<script src="/vendor/devdojo/chatter/assets/js/chatter.js"></script>
<script>
	var my_tinymce = tinyMCE;
	$('document').ready(function(){

		$('#tinymce_placeholder').click(function(){
			my_tinymce.activeEditor.focus();
		});
		$('.chatter-close').click(function(){
			$('#new_discussion').slideUp();
		});
		$('#new_discussion_btn, #cancel_discussion').click(function(){
			@if(Auth::guest())
				window.location.href = "/{{ Config::get('chatter.routes.home') }}/login";
			@else
				$('#new_discussion').slideDown();
				$('#title').focus();
			@endif
		});

		$("#color").spectrum({
		    color: "#333639",
		    preferredFormat: "hex",
		    containerClassName: 'chatter-color-picker',
		    cancelText: '',
    		chooseText: 'close',
		    move: function(color) {
				$("#color").val(color.toHexString());
			}
		});

		@if (count($errors) > 0)
			$('#new_discussion').slideDown();
			$('#title').focus();
		@endif


	});
</script>
@stop
