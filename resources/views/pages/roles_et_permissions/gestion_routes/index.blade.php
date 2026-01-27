@extends('layouts.demo1.base')

@section('content')
      <!-- Container -->
      <div class="kt-container-fixed">
      <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
       <div class="flex flex-col justify-center gap-2">
        <h1 class="text-xl font-medium leading-none text-mono">
         routes
        </h1>
        <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
         Overview of all team members and routes.
        </div>
       </div>
       <div class="flex items-center gap-2.5">
        <a class="kt-btn kt-btn-outline" href="#">
         New routes
        </a>
       </div>
      </div>
     </div>
     <!-- End of Container -->
     <div class="kt-container-fixed">
      <div class="grid gap-5 lg:gap-7.5">
       <div class="kt-card">
        <div class="kt-card-header">
         <h3 class="kt-card-title">
          Role Permissions for
          <a class="text-primary" href="#">
           Project Manager
          </a>
         </h3>
        </div>
        <div class="kt-card-content grid grid-cols-1 lg:grid-cols-2 gap-5 py-5 lg:py-7.5">
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-category text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Workspace Settings
            </span>
            <span class="text-sm text-secondary-foreground">
             Users may view and update the settings of the workspace.
            </span>
           </div>
          </div>
          <input checked="" class="kt-switch kt-switch-sm" name="param" type="checkbox" value="1"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-two-credit-cart text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Billing Management
            </span>
            <span class="text-sm text-secondary-foreground">
             Users are authorized to review, update subscriptions.
            </span>
           </div>
          </div>
          <input class="kt-switch kt-switch-sm" name="param" type="checkbox" value="2"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-mouse-square text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Integration Setup
            </span>
            <span class="text-sm text-secondary-foreground">
             Manage user integrations and associated tags.
            </span>
           </div>
          </div>
          <input checked="" class="kt-switch kt-switch-sm" name="param" type="checkbox" value="3"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-toggle-off-circle text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Permissions Control
            </span>
            <span class="text-sm text-secondary-foreground">
             Grant or revoke user access and tags.
            </span>
           </div>
          </div>
          <input class="kt-switch kt-switch-sm" name="param" type="checkbox" value="4"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-map text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Map Creation
            </span>
            <span class="text-sm text-secondary-foreground">
             Initiate new mapping projects within workspace.
            </span>
           </div>
          </div>
          <input class="kt-switch kt-switch-sm" name="param" type="checkbox" value="5"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-exit-up text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Data Export
            </span>
            <span class="text-sm text-secondary-foreground">
             Allow extraction of workspace data for analysis.
            </span>
           </div>
          </div>
          <input checked="" class="kt-switch kt-switch-sm" name="param" type="checkbox" value="6"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-security-user text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             User Roles
            </span>
            <span class="text-sm text-secondary-foreground">
             Update roles and permissions for map users.
            </span>
           </div>
          </div>
          <input checked="" class="kt-switch kt-switch-sm" name="param" type="checkbox" value="7"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-shield-tick text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Security Settings
            </span>
            <span class="text-sm text-secondary-foreground">
             Adjust workspace security protocols and measures.
            </span>
           </div>
          </div>
          <input checked="" class="kt-switch kt-switch-sm" name="param" type="checkbox" value="8"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-key-square text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Insights Access
            </span>
            <span class="text-sm text-secondary-foreground">
             View workspace analytics and performance data.
            </span>
           </div>
          </div>
          <input class="kt-switch kt-switch-sm" name="param" type="checkbox" value="9"/>
         </div>
         <div class="rounded-xl border border-border p-4 flex items-center justify-between gap-2.5">
          <div class="flex items-center gap-3.5">
           <div class="relative size-[45px] shrink-0">
            <svg class="w-full h-full stroke-border fill-muted/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
             <i class="ki-filled ki-shop text-lg text-muted-foreground">
             </i>
            </div>
           </div>
           <div class="flex flex-col gap-1">
            <span class="flex items-center gap-1.5 leading-none font-medium text-sm text-mono">
             Merchant List
            </span>
            <span class="text-sm text-secondary-foreground">
             Update and manage merchant associations.
            </span>
           </div>
          </div>
          <input class="kt-switch kt-switch-sm" name="param" type="checkbox" value="10"/>
         </div>
        </div>
        <div class="kt-card-footer justify-center">
         <a class="kt-btn kt-btn-outline" href="#">
          New Permission
         </a>
        </div>
       </div>
     
      </div>
     </div>
@endsection
