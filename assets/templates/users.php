<!DOCTYPE html>
<html lang="en">
<!-- Begin Header -->
<head>
	<?php wp_head(); ?>
</head>
<!-- End Header -->

<!-- Begin Body -->
<body class="header--fixed header-mobile--fixed subheader--enabled subheader--fixed subheader--solid">

<div class="wrapper">
	<div class="content">
		<div class="container container--fluid">

			<!-- Begin Intro -->
			<div class="row justify-content-center">
				<div class="col-lg-10">
					<div class="alert alert-light alert-elevate" role="alert">
						<div class="alert-icon"><i class="flaticon-information font-brand"></i></div>
						<div class="alert-text">
							<?php esc_html_e( 'The user data will automatically be retrieved upon finishing the page load. You can verify this by disabling JavaScript. To get a fresh copy of the user data, press the "Flush" button.' ); ?>
						</div>
						<div class="separator separator--dashed"></div>
					</div>
				</div>
			</div>
			<!-- End Intro -->

			<!-- Begin User Section -->
			<div class="row justify-content-center">
				<!-- Begin User List -->
				<div class="col-lg-6">
					<div class="portlet portlet--mobile">
						<!-- Begin User List Header -->
						<div class="portlet__head">
							<div class="portlet__head-label">
								<h3 class="portlet__head-title"><?php esc_html_e( 'User Data', 'inpsyde' ); ?></h3>
							</div>
							<div class="portlet__head-toolbar">
								<div class="portlet__head-actions">
									<a href="#" class="btn btn-danger btn-sm" id="inpsyde-flush-users">
										<i class="flaticon-delete text-white"></i>
										<?php esc_html_e( 'Flush', 'inpsyde' ); ?>
									</a>
								</div>
							</div>
						</div>
						<!-- End User List Header -->

						<!-- Begin Table Content -->
						<div class="portlet__body" id="inpsyde-table-wrapper">
							<table class="table table-striped- table-bordered table-hover table-checkable" id="inpsyde-table">
								<thead>
								<tr>
									<th><?php esc_html_e( 'ID', 'inpsyde' ); ?></th>
									<th><?php esc_html_e( 'Name', 'inpsyde' ); ?></th>
									<th><?php esc_html_e( 'Username', 'inpsyde' ); ?></th>
									<th><?php esc_html_e( 'Email', 'inpsyde' ); ?></th>
									<th><?php esc_html_e( 'Phone', 'inpsyde' ); ?></th>
									<th><?php esc_html_e( 'Website', 'inpsyde' ); ?></th>
									<th><?php esc_html_e( 'Actions', 'inpsyde' ); ?></th>
								</tr>
								</thead>
							</table>
						</div>
						<!-- End Table Content -->
					</div>
				</div>
				<!-- End User List -->

				<!-- Begin User Details -->
				<div class="col-lg-4">
					<div class="portlet portlet--mobile">
						<!-- Begin User List Header -->
						<div class="portlet__head">
							<div class="portlet__head-label">
								<h3 class="portlet__head-title"><?php esc_html_e( 'User Details', 'inpsyde' ); ?></h3>
							</div>
						</div>
						<!-- End User List Header -->
						<div class="portlet__body">
							<div class="section section--last">
								<?php esc_html_e( 'To view a user\'s details, click the "View" button in each row. Click the "Refresh" button above to clear the cache and fetch a fresh copy.', 'inpsyde' ); ?>
							</div>
						</div>

						<!-- Begin User Details -->
						<div class="portlet portlet--height-fluid" id="inpsyde-user-details">
							<div class="portlet__head portlet__head--noborder">
								<div class="portlet__head-label">
									<h3 class="portlet__head-title"></h3>
								</div>
							</div>
							<div class="portlet__body">

								<!--begin::Widget -->
								<div class="widget widget--user-profile-2">
									<div class="widget__head">
										<div class="widget__media">
											<img class="widget__img hidden-" src="#" alt="#" id="inpsyde-user-avatar">
											<div class="widget__pic widget__pic--success font-success font-boldest hidden">#</div>
										</div>
										<div class="widget__info">
											<a href="#" class="widget__username" id="inpsyde-name">#</a>
											<span class="widget__desc" id="inpsyde-user-company">#</span>
										</div>
									</div>
									<div class="widget__body">
										<div class="widget__section" id="inpsyde-user-address">
											<?php esc_html_e( 'User Address', 'inpsyde' ); ?>
										</div>
										<div class="widget__item">
											<div class="widget__contact">
												<span class="widget__label"><?php esc_html_e( 'Email:', 'inpsyde' ); ?></span>
												<a href="#" class="widget__data" id="inpsyde-user-email">#</a>
											</div>
											<div class="widget__contact">
												<span class="widget__label"><?php esc_html_e( 'Phone:', 'inpsyde' ); ?></span>
												<a href="#" class="widget__data" id="inpsyde-user-phone">#</a>
											</div>
											<div class="widget__contact">
												<span class="widget__label"><?php esc_html_e( 'City:', 'inpsyde' ); ?></span>
												<span class="widget__data" id="inpsyde-user-location">#</span>
											</div>
											<div class="widget__contact">
												<span class="widget__label"><?php esc_html_e( 'Website:', 'inpsyde' ); ?></span>
												<span class="widget__data" id="inpsyde-user-website">#</span>
											</div>
										</div>
									</div>
									<div class="widget__footer">
										<a href="#" class="btn btn-info btn-lg btn-upper" id="inpsyde-refresh-details" data-id="0">
											<i class="flaticon-refresh text-white"></i>
											<?php esc_html_e( 'Refresh', 'inpsyde' ); ?>
										</a>
									</div>
								</div>

								<!--end::Widget -->
							</div>
						</div>
						<!-- End User Details -->
					</div>
				</div>
				<!-- End User List -->
			</div>
			<!-- End User Section -->
		</div>

	</div>

	<?php wp_footer(); ?>
</body>
<!-- End Body -->
</html>
