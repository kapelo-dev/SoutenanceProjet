@extends('layouts.demo1.base')

@section('content')
      <!-- Container -->
      <div class="kt-container-fixed">
      <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
       <div class="flex flex-col justify-center gap-2">
        <h1 class="text-xl font-medium leading-none text-mono">
         Roles
        </h1>
        <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
         Overview of all team members and roles.
        </div>
       </div>
       <div class="flex items-center gap-2.5">
        <a class="kt-btn kt-btn-outline" href="#">
         New Role
        </a>
       </div>
      </div>
     </div>
     <!-- End of Container -->
     <!-- Container -->
     <div class="kt-container-fixed">
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-5 lg:gap-7.5">
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full stroke-primary/10 fill-primary/5" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled ki-setting text-xl text-primary">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <a class="text-base font-medium text-mono hover:text-primary mb-px" href="html/demo1/public-profile/profiles/creator.html">
            Administrator
           </a>
           <span class="text-sm text-secondary-foreground">
            Default role
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" data-kt-modal-toggle="#share_profile_modal" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-share">
               </i>
              </span>
              <span class="kt-menu-title">
               Share
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-file-up">
               </i>
              </span>
              <span class="kt-menu-title">
               Export
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         Manages system settings and user access, ensures system stability.
        </p>
        <span class="text-sm text-foreground">
         1 person
        </span>
       </div>
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full stroke-primary/10 fill-primary/5" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled ki-eye text-xl text-primary">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <a class="text-base font-medium text-mono hover:text-primary mb-px" href="html/demo1/public-profile/profiles/company.html">
            Viewer
           </a>
           <span class="text-sm text-secondary-foreground">
            Default role
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" data-kt-modal-toggle="#share_profile_modal" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-share">
               </i>
              </span>
              <span class="kt-menu-title">
               Share
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-file-up">
               </i>
              </span>
              <span class="kt-menu-title">
               Export
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         Can view data but doesn't have editing privileges.
        </p>
        <span class="text-sm text-foreground">
         32 people
        </span>
       </div>
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full stroke-green-200 dark:stroke-green-950 dark:ring-green-950 fill-green-50 dark:fill-green-950/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled ki-face-id text-xl text-green-500">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <a class="text-base font-medium text-mono hover:text-primary mb-px" href="html/demo1/public-profile/profiles/nft.html">
            Remote Developer
           </a>
           <span class="text-sm text-secondary-foreground">
            Remote role
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" data-kt-modal-toggle="#share_profile_modal" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-share">
               </i>
              </span>
              <span class="kt-menu-title">
               Share
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-file-up">
               </i>
              </span>
              <span class="kt-menu-title">
               Export
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         Provides assistance and resolves customer inquiries and issues.
        </p>
        <span class="text-sm text-foreground">
         6 people
        </span>
       </div>
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full stroke-destructive/10 fill-destructive/5" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled ki-delivery-24 text-xl text-destructive">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <a class="text-base font-medium text-mono hover:text-primary mb-px" href="html/demo1/public-profile/profiles/blogger.html">
            Customer Support
           </a>
           <span class="text-sm text-secondary-foreground">
            Default role
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" data-kt-modal-toggle="#share_profile_modal" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-share">
               </i>
              </span>
              <span class="kt-menu-title">
               Share
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-file-up">
               </i>
              </span>
              <span class="kt-menu-title">
               Export
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         Provides assistance and resolves customer inquiries and issues.
        </p>
        <span class="text-sm text-foreground">
         32 people
        </span>
       </div>
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full stroke-violet-100 dark:stroke-violet-950 ring-violet-50 dark:ring-violet-950 fill-violet-50 dark:fill-violet-950/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled ki-chart-line-up-2 text-xl text-violet-500">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <a class="text-base font-medium text-mono hover:text-primary mb-px" href="html/demo1/public-profile/profiles/crm.html">
            Project Manager
           </a>
           <span class="text-sm text-secondary-foreground">
            Default role
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" data-kt-modal-toggle="#share_profile_modal" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-share">
               </i>
              </span>
              <span class="kt-menu-title">
               Share
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-file-up">
               </i>
              </span>
              <span class="kt-menu-title">
               Export
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         Oversees projects, ensures they're on time and within budget.
        </p>
        <span class="text-sm text-foreground">
         6 people
        </span>
       </div>
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full stroke-input fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled ki-design-1 text-xl text-muted-foreground">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <a class="text-base font-medium text-mono hover:text-primary mb-px" href="html/demo1/public-profile/profiles/gamer.html">
            Remote Designer
           </a>
           <span class="text-sm text-secondary-foreground">
            Remote role
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" data-kt-modal-toggle="#share_profile_modal" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-share">
               </i>
              </span>
              <span class="kt-menu-title">
               Share
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-file-up">
               </i>
              </span>
              <span class="kt-menu-title">
               Export
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         Creates visual designs remotely for various projects.
        </p>
        <span class="text-sm text-foreground">
         6 people
        </span>
       </div>
       <div class="kt-card flex flex-col gap-5 p-5 lg:p-7.5">
        <div class="flex items-center flex-wrap justify-between gap-1">
         <div class="flex items-center gap-2.5">
          <div class="relative size-[44px] shrink-0">
           <svg class="w-full h-full stroke-green-200 dark:stroke-green-950 dark:ring-green-950 fill-green-50 dark:fill-green-950/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
            </path>
            <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
            </path>
           </svg>
           <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
            <i class="ki-filled ki-people text-xl text-green-500">
            </i>
           </div>
          </div>
          <div class="flex flex-col">
           <a class="text-base font-medium text-mono hover:text-primary mb-px" href="html/demo1/public-profile/profiles/feeds.html">
            HR Manager
           </a>
           <span class="text-sm text-secondary-foreground">
            Default role
           </span>
          </div>
         </div>
         <div class="kt-menu inline-flex" data-kt-menu="true">
          <div class="kt-menu-item" data-kt-menu-item-offset="0, 10px" data-kt-menu-item-placement="bottom-end" data-kt-menu-item-placement-rtl="bottom-start" data-kt-menu-item-toggle="dropdown" data-kt-menu-item-trigger="click">
           <button class="kt-menu-toggle kt-btn kt-btn-sm kt-btn-icon kt-btn-ghost">
            <i class="ki-filled ki-dots-vertical text-lg">
            </i>
           </button>
           <div class="kt-menu-dropdown kt-menu-default w-full max-w-[175px]" data-kt-menu-dismiss="true">
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-document">
               </i>
              </span>
              <span class="kt-menu-title">
               Details
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" data-kt-modal-toggle="#share_profile_modal" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-share">
               </i>
              </span>
              <span class="kt-menu-title">
               Share
              </span>
             </a>
            </div>
            <div class="kt-menu-item">
             <a class="kt-menu-link" href="#">
              <span class="kt-menu-icon">
               <i class="ki-filled ki-file-up">
               </i>
              </span>
              <span class="kt-menu-title">
               Export
              </span>
             </a>
            </div>
           </div>
          </div>
         </div>
        </div>
        <p class="text-sm text-secondary-foreground">
         Manages human resources, recruitment, and employee relations.
        </p>
        <span class="text-sm text-foreground">
         1 person
        </span>
       </div>
       <style>
        .add-new-bg {
		background-image: url('assets/media/images/2600x1200/bg-4.png');
	}
	.dark .add-new-bg {
		background-image: url('assets/media/images/2600x1200/bg-4-dark.png');
	}
       </style>
       <a class="kt-card border-2 border-dashed border-primary/10 bg-center bg-[length:600px] bg-no-repeat add-new-bg" href="html/demo1/public-profile/works.html">
        <div class="kt-card-content grid items-center">
         <div class="flex flex-col gap-3">
          <div class="flex justify-center pt-5">
           <div class="relative size-[60px] shrink-0">
            <svg class="w-full h-full stroke-primary/10 fill-primary/5" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
             <path d="M16 2.4641C19.7128 0.320509 24.2872 0.320508 28 2.4641L37.6506 8.0359C41.3634 10.1795 43.6506 14.141 43.6506 
			18.4282V29.5718C43.6506 33.859 41.3634 37.8205 37.6506 39.9641L28 45.5359C24.2872 47.6795 19.7128 47.6795 16 45.5359L6.34937 
			39.9641C2.63655 37.8205 0.349365 33.859 0.349365 29.5718V18.4282C0.349365 14.141 2.63655 10.1795 6.34937 8.0359L16 2.4641Z" fill="">
             </path>
             <path d="M16.25 2.89711C19.8081 0.842838 24.1919 0.842837 27.75 2.89711L37.4006 8.46891C40.9587 10.5232 43.1506 14.3196 43.1506 
			18.4282V29.5718C43.1506 33.6804 40.9587 37.4768 37.4006 39.5311L27.75 45.1029C24.1919 47.1572 19.8081 47.1572 16.25 45.1029L6.59937 
			39.5311C3.04125 37.4768 0.849365 33.6803 0.849365 29.5718V18.4282C0.849365 14.3196 3.04125 10.5232 6.59937 8.46891L16.25 2.89711Z" stroke="">
             </path>
            </svg>
            <div class="absolute leading-none start-2/4 top-2/4 -translate-y-2/4 -translate-x-2/4 rtl:translate-x-2/4">
             <i class="ki-filled ki-rocket text-2xl text-primary">
             </i>
            </div>
           </div>
          </div>
          <div class="flex flex-col text-center">
           <span class="text-lg font-medium text-mono hover:text-primary mb-px">
            Add New Role
           </span>
           <span class="text-sm text-secondary-foreground">
            Ignite Professional Adventures
           </span>
          </div>
         </div>
        </div>
       </a>
      </div>
     </div>
@endsection
