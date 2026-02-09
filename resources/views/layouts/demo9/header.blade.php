<!-- Header -->
<header class="flex items-center transition-[height] shrink-0 bg-background h-(--header-height)" data-kt-sticky="true" data-kt-sticky-class="transition-[height] fixed z-10 top-0 left-0 right-0 shadow-xs backdrop-blur-md bg-background/70 border border-border" data-kt-sticky-name="header" data-kt-sticky-offset="100px" id="header">
	<!-- Container -->
	<div class="kt-container-fixed flex lg:justify-between items-center gap-2.5">
		<!-- Logo -->
		<div class="flex items-center gap-1 lg:w-[400px] grow lg:grow-0">
			<button class="kt-btn kt-btn-icon kt-btn-ghost -ms-2.5 lg:hidden" data-kt-drawer-toggle="#navbar">
				<i class="ki-filled ki-menu">
				</i>
			</button>
			<div class="flex items-center gap-2">
				<a class="flex items-center shrink-0" href="{{ url('/') }}" title="PDV Connect">
					<img class="dark:hidden w-8 shrink-0" src="{{ asset('assets/media/app/mini-logo.svg') }}" alt="PDV Connect" />
					<img class="hidden dark:inline-block w-8 shrink-0" src="{{ asset('assets/media/app/mini-logo-dark.svg') }}" alt="PDV Connect" />
				</a>
				<h3 class="text-mono text-lg font-medium hidden md:block">
					PDV Connect
				</h3>
			</div>
			
		</div>
	
		<!-- End of Mobile Search -->
		
	</div>
	<!-- End of Container -->
</header>
<!-- End of Header -->