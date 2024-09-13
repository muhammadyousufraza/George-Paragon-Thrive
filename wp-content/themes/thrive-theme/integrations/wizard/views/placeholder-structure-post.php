<?php $preview_posts = isset( $attr['preview_posts'] ) ? $attr['preview_posts'] : 1; ?>
<div class="ttb-wizard-layout-holder">
	<div class="ttb-placeholder p-top-section p-box" style="height: 66px;"></div>
	<div class="ttb-placeholder p-layout mt-40 ttb-width pb-20">
		<div class="p-content">
			<?php foreach ( range( 1, $preview_posts ) as $index ) : ?>
				<div class="p-box" style="height: 350px"></div>
				<div class="p-box mt-10 mb-15" style="width: 55%; height: 20px"></div>
				<?php foreach ( range( 1, 40 ) as $i ) : ?>
					<div class="p-box mt-5" style="height: 10px"></div>
				<?php endforeach ?>
				<div class="d-flex mt-30 mb-40">
					<div class="square"></div>
					<div class="square ml-5"></div>
					<div class="square ml-5"></div>
					<div class="flex-self-end d-flex">
						<div class="p-box" style="height: 10px; width: 30px"></div>
						<div class="p-box ml-10" style="width: 105px; height: 10px;"></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="p-sidebar">
			<?php foreach ( range( 1, 6 ) as $i ) : ?>
				<div class="p-box" style="height: 15px;width:70%"></div>
				<div class="p-box mt-15" style="height: 20px;width: 80%"></div>
				<div class="p-box mt-1" style="height: 20px;width: 80%"></div>
				<div class="p-box mt-1" style="height: 20px;width: 80%"></div>
				<div class="p-box mt-1" style="height: 20px;width: 80%"></div>
				<div class="p-box mt-1" style="height: 20px;width: 80%"></div>
				<div class="mt-40 p-columns cols-3" style="height: 15px">
					<div class="p-box"></div>
					<div class="p-box"></div>
					<div class="p-box"></div>
				</div>
				<?php foreach ( range( 1, 3 ) as $i ) : ?>
					<div class="sidebar-list-item mt-20">
						<div class="icon p-box"></div>
						<div class="text ml-5">
							<div class="p-box mb-15" style="height: 10px"></div>
							<div class="p-box" style="height: 6px;"></div>
							<div class="p-box mt-1" style="height: 6px;"></div>
							<div class="p-box mt-15" style="height: 4px;"></div>
						</div>
					</div>
				<?php endforeach; ?>
				<div class="p-box" style="margin-top: 30px; height: 15px;width: 60%"></div>
				<div class="p-box mt-10" style="height: 150px;"></div>
				<div class="p-box mt-20" style="height: 10px; width: 70%"></div>
				<div class="p-box mt-10" style="height: 6px;"></div>
				<div class="p-box mt-10" style="height: 6px;"></div>
				<div class="p-box mt-10" style="height: 6px;"></div>
				<div class="p-box mt-10" style="height: 6px;"></div>
				<div class="p-box mt-10 mb-20" style="height: 6px;"></div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
