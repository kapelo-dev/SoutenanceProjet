@extends('layouts.demo1.base')

@section('content')
<main class="grow" id="content" role="content">
    <!-- Container -->
    <div class="kt-container-fixed" id="contentContainer">
    </div>
    <!-- End of Container -->
    <!-- Container -->
    <div class="kt-container-fixed">
     <div class="flex flex-col items-stretch gap-5 lg:gap-7.5">
      <div class="flex flex-wrap items-center gap-5 justify-between">
       <h3 class="text-base text-mono font-medium">
        Showing 6 Users
       </h3>
       <div class="flex items-center flex-wrap gap-5">
        <div class="flex items-center gap-2.5">
         <select class="kt-select w-36" data-kt-select="true" data-kt-select-placeholder="Select a status">
          <option value="1">
           Active
          </option>
          <option value="2">
           Disabled
          </option>
          <option value="2">
           Pending
          </option>
         </select>
         <select class="kt-select w-36" data-kt-select="true" data-kt-select-placeholder="Select a sort">
          <option value="1">
           Latest
          </option>
          <option value="2">
           Older
          </option>
          <option value="3">
           Oldest
          </option>
         </select>
         <button class="kt-btn kt-btn-outline kt-btn-primary">
          <i class="ki-filled ki-setting-4">
          </i>
          Filters
         </button>
        </div>
        <div class="flex">
         <label class="kt-input">
          <i class="ki-filled ki-magnifier">
          </i>
          <input placeholder="Type name, team" type="text" value=""/>
         </label>
        </div>
        <div class="kt-toggle-group kt-toggle-group-sm" data-kt-tabs="true">
         <a class="kt-btn kt-btn-icon active" data-kt-tab-toggle="#team_crew_card" href="#">
          <i class="ki-filled ki-category">
          </i>
         </a>
         <a class="kt-btn kt-btn-icon" data-kt-tab-toggle="#team_crew_list" href="#">
          <i class="ki-filled ki-row-horizontal">
          </i>
         </a>
        </div>
       </div>
      </div>
      <div class="flex flex-col gap-5 lg:gap-7.5" id="team_crew_card">
       <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
        <div class="kt-card">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5">
          <div class="flex justify-center mb-2.5">
           <div class="size-20 relative">
            <img class="rounded-full" src="{{ asset('assets/media/avatars/300-1.png') }}"/>
            <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
            Jenny Klabber
           </a>
           <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
            </path>
           </svg>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            Pinnacle Innovate
           </div>
           <div class="flex items-center text-sm">
            <i class="ki-filled ki-sms me-1 text-muted-foreground">
            </i>
            <a class="text-secondary-foreground hover:text-primary" href="#">
             kevin@pinnacle.com
            </a>
           </div>
          </div>
          <div class="grid justify-center gap-1.5 mb-7.5">
           <span class="text-xs uppercase text-secondary-foreground text-center">
            team
           </span>
           <div class="flex -space-x-2">
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-4.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-1.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-2.png') }}"/>
            </div>
            <div class="flex">
             <span class="relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 font-semibold leading-none text-2xs size-7 text-white ring-background bg-green-500">
              +10
             </span>
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             92
            </span>
            <span class="text-secondary-foreground text-xs">
             Purchases
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $69
            </span>
            <span class="text-secondary-foreground text-xs">
             Avg. Price
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $6,240
            </span>
            <span class="text-secondary-foreground text-xs">
             Total spent
            </span>
           </div>
          </div>
         </div>
         <div class="kt-card-footer justify-center">
          <a class="kt-btn kt-btn-outline">
           <i class="ki-filled ki-check-circle">
           </i>
           Connected
          </a>
         </div>
        </div>
        <div class="kt-card">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5">
          <div class="flex justify-center mb-2.5">
           <div class="flex items-center justify-center relative text-2xl text-green-500 size-20 ring-1 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30 rounded-full">
            S
            <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
            Sarah Johnson
           </a>
           <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
            </path>
           </svg>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            InnovateX
           </div>
           <div class="flex items-center text-sm">
            <i class="ki-filled ki-sms me-1 text-muted-foreground">
            </i>
            <a class="text-secondary-foreground hover:text-primary" href="#">
             sarahj@innx.com
            </a>
           </div>
          </div>
          <div class="grid justify-center gap-1.5 mb-7.5">
           <span class="text-xs uppercase text-secondary-foreground text-center">
            team
           </span>
           <div class="flex -space-x-2">
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-6.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-7.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-11.png') }}"/>
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             123
            </span>
            <span class="text-secondary-foreground text-xs">
             Purchases
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $30
            </span>
            <span class="text-secondary-foreground text-xs">
             Avg. Price
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $3,713
            </span>
            <span class="text-secondary-foreground text-xs">
             Total spent
            </span>
           </div>
          </div>
         </div>
         <div class="kt-card-footer justify-center">
          <a class="kt-btn kt-btn-primary">
           <i class="ki-filled ki-users">
           </i>
           Connect
          </a>
         </div>
        </div>
        <div class="kt-card">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5">
          <div class="flex justify-center mb-2.5">
           <div class="flex items-center justify-center relative text-2xl text-destructive size-20 ring-1 ring-destructive/20 bg-destructive/5 rounded-full">
            K
            <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
            Kevin Wang
           </a>
           <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
            </path>
           </svg>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            Pinnacle Innovate
           </div>
           <div class="flex items-center text-sm">
            <i class="ki-filled ki-sms me-1 text-muted-foreground">
            </i>
            <a class="text-secondary-foreground hover:text-primary" href="#">
             kevin@pinnacle.com
            </a>
           </div>
          </div>
          <div class="grid justify-center gap-1.5 mb-7.5">
           <span class="text-xs uppercase text-secondary-foreground text-center">
            team
           </span>
           <div class="flex -space-x-2">
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-29.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-33.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-23.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-31.png') }}"/>
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             30
            </span>
            <span class="text-secondary-foreground text-xs">
             Purchases
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $150
            </span>
            <span class="text-secondary-foreground text-xs">
             Avg. Price
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $4,500
            </span>
            <span class="text-secondary-foreground text-xs">
             Total spent
            </span>
           </div>
          </div>
         </div>
         <div class="kt-card-footer justify-center">
          <a class="kt-btn kt-btn-outline">
           <i class="ki-filled ki-check-circle">
           </i>
           Connected
          </a>
         </div>
        </div>
        <div class="kt-card">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5">
          <div class="flex justify-center mb-2.5">
           <div class="size-20 relative">
            <img class="rounded-full" src="assets/media/avatars/300-9.png') }}"/>
            <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
            Brian Davis
           </a>
           <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
            </path>
           </svg>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            Vortex Tech
           </div>
           <div class="flex items-center text-sm">
            <i class="ki-filled ki-sms me-1 text-muted-foreground">
            </i>
            <a class="text-secondary-foreground hover:text-primary" href="#">
             brian@vortextech.com
            </a>
           </div>
          </div>
          <div class="grid justify-center gap-1.5 mb-7.5">
           <span class="text-xs uppercase text-secondary-foreground text-center">
            team
           </span>
           <div class="flex -space-x-2">
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-14.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-3.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-19.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-15.png') }}"/>
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             87
            </span>
            <span class="text-secondary-foreground text-xs">
             Purchases
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $22
            </span>
            <span class="text-secondary-foreground text-xs">
             Avg. Price
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $1958
            </span>
            <span class="text-secondary-foreground text-xs">
             Total spent
            </span>
           </div>
          </div>
         </div>
         <div class="kt-card-footer justify-center">
          <a class="kt-btn kt-btn-outline">
           <i class="ki-filled ki-check-circle">
           </i>
           Connected
          </a>
         </div>
        </div>
        <div class="kt-card">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5">
          <div class="flex justify-center mb-2.5">
           <div class="flex items-center justify-center relative text-2xl text-green-500 size-20 ring-1 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30 rounded-full">
            M
            <div class="flex size-2.5 bg-violet-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
            Megan Taylor
           </a>
           <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
            </path>
           </svg>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            Catalyst
           </div>
           <div class="flex items-center text-sm">
            <i class="ki-filled ki-sms me-1 text-muted-foreground">
            </i>
            <a class="text-secondary-foreground hover:text-primary" href="#">
             megan@catalyst.com
            </a>
           </div>
          </div>
          <div class="grid justify-center gap-1.5 mb-7.5">
           <span class="text-xs uppercase text-secondary-foreground text-center">
            team
           </span>
           <div class="flex -space-x-2">
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-26.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-6.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-1.png') }}"/>
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             45
            </span>
            <span class="text-secondary-foreground text-xs">
             Purchases
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $300
            </span>
            <span class="text-secondary-foreground text-xs">
             Avg. Price
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $13,500
            </span>
            <span class="text-secondary-foreground text-xs">
             Total spent
            </span>
           </div>
          </div>
         </div>
         <div class="kt-card-footer justify-center">
          <a class="kt-btn kt-btn-primary">
           <i class="ki-filled ki-users">
           </i>
           Connect
          </a>
         </div>
        </div>
        <div class="kt-card">
         <div class="kt-card-content lg:pt-9 lg:pb-7.5">
          <div class="flex justify-center mb-2.5">
           <div class="size-20 relative">
            <img class="rounded-full" src="assets/media/avatars/300-8.png') }}"/>
            <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center gap-1.5 mb-2.5">
           <a class="hover:text-primary text-base leading-5 font-medium text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
            Alex Martinez
           </a>
           <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
            <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
            </path>
           </svg>
          </div>
          <div class="flex flex-wrap justify-center items-center gap-4 mb-7">
           <div class="flex items-center text-sm text-secondary-foreground">
            <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
            </i>
            Precision Solutions
           </div>
           <div class="flex items-center text-sm">
            <i class="ki-filled ki-sms me-1 text-muted-foreground">
            </i>
            <a class="text-secondary-foreground hover:text-primary" href="#">
             alex@kteam.com
            </a>
           </div>
          </div>
          <div class="grid justify-center gap-1.5 mb-7.5">
           <span class="text-xs uppercase text-secondary-foreground text-center">
            team
           </span>
           <div class="flex -space-x-2">
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-4.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
            </div>
            <div class="flex">
             <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-11.png') }}"/>
            </div>
            <div class="flex">
             <span class="relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 font-semibold leading-none text-2xs size-7 text-white ring-background bg-green-500">
              +10
             </span>
            </div>
           </div>
          </div>
          <div class="flex items-center justify-center flex-wrap gap-2 lg:gap-5">
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             63
            </span>
            <span class="text-secondary-foreground text-xs">
             Purchases
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $65
            </span>
            <span class="text-secondary-foreground text-xs">
             Avg. Price
            </span>
           </div>
           <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input rounded-md px-2.5 py-2 shrink-0 min-w-24 max-w-auto">
            <span class="text-mono text-sm leading-none font-medium">
             $4,095
            </span>
            <span class="text-secondary-foreground text-xs">
             Total spent
            </span>
           </div>
          </div>
         </div>
         <div class="kt-card-footer justify-center">
          <a class="kt-btn kt-btn-outline">
           <i class="ki-filled ki-check-circle">
           </i>
           Connected
          </a>
         </div>
        </div>
       </div>
       <div class="flex justify-center">
        <a class="kt-link kt-link-underlined kt-link-dashed" href="html/demo2/public-profile/projects/3-columns.html">
         Show more projects
        </a>
       </div>
      </div>
      <div class="hidden" id="team_crew_list">
       <div class="grid grid-cols-1 gap-5 lg:gap-7.5">
        <div class="kt-card p-7.5">
         <div class="flex items-center flex-wrap justify-between gap-5">
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            <div class="size-20 relative">
             <img class="rounded-full" src="{{ asset('assets/media/avatars/300-1.png') }}"/>
             <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
              Jenny Klabber
             </a>
             <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
              </path>
             </svg>
            </div>
            <div class="flex items-center flex-wrap gap-x-4">
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
              </i>
              Pinnacle Innovate
             </div>
             <div class="flex items-center text-sm">
              <i class="ki-filled ki-sms me-1 text-muted-foreground">
              </i>
              <a class="text-secondary-foreground hover:text-primary" href="#">
               kevin@pinnacle.com
              </a>
             </div>
            </div>
           </div>
          </div>
          <div class="flex items-center flex-wrap gap-5 lg:gap-11">
           <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              92
             </span>
             <span class="text-secondary-foreground text-xs">
              Purchases
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $69
             </span>
             <span class="text-secondary-foreground text-xs">
              Avg. Price
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $6,240
             </span>
             <span class="text-secondary-foreground text-xs">
              Total spent
             </span>
            </div>
           </div>
           <div>
            <div class="flex -space-x-2">
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-4.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-1.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-2.png') }}"/>
             </div>
             <div class="flex">
              <span class="relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 font-semibold leading-none text-2xs size-7 text-white ring-background bg-green-500">
               +10
              </span>
             </div>
            </div>
           </div>
           <div class="text-right w-28">
            <a class="kt-btn kt-btn-outline">
             <i class="ki-filled ki-check-circle">
             </i>
             Connected
            </a>
           </div>
          </div>
         </div>
        </div>
        <div class="kt-card p-7.5">
         <div class="flex items-center flex-wrap justify-between gap-5">
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            <div class="flex items-center justify-center relative text-2xl text-green-500 size-20 ring-1 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30 rounded-full">
             S
             <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
              Sarah Johnson
             </a>
             <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
              </path>
             </svg>
            </div>
            <div class="flex items-center flex-wrap gap-x-4">
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
              </i>
              InnovateX
             </div>
             <div class="flex items-center text-sm">
              <i class="ki-filled ki-sms me-1 text-muted-foreground">
              </i>
              <a class="text-secondary-foreground hover:text-primary" href="#">
               sarahj@innx.com
              </a>
             </div>
            </div>
           </div>
          </div>
          <div class="flex items-center flex-wrap gap-5 lg:gap-11">
           <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              123
             </span>
             <span class="text-secondary-foreground text-xs">
              Purchases
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $30
             </span>
             <span class="text-secondary-foreground text-xs">
              Avg. Price
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $3,713
             </span>
             <span class="text-secondary-foreground text-xs">
              Total spent
             </span>
            </div>
           </div>
           <div>
            <div class="flex -space-x-2">
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-6.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-7.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-11.png') }}"/>
             </div>
            </div>
           </div>
           <div class="text-right w-28">
            <a class="kt-btn kt-btn-primary">
             <i class="ki-filled ki-users">
             </i>
             Connect
            </a>
           </div>
          </div>
         </div>
        </div>
        <div class="kt-card p-7.5">
         <div class="flex items-center flex-wrap justify-between gap-5">
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            <div class="flex items-center justify-center relative text-2xl text-destructive size-20 ring-1 ring-destructive/20 bg-destructive/5 rounded-full">
             K
             <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
              Kevin Wang
             </a>
             <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
              </path>
             </svg>
            </div>
            <div class="flex items-center flex-wrap gap-x-4">
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
              </i>
              Pinnacle Innovate
             </div>
             <div class="flex items-center text-sm">
              <i class="ki-filled ki-sms me-1 text-muted-foreground">
              </i>
              <a class="text-secondary-foreground hover:text-primary" href="#">
               kevin@pinnacle.com
              </a>
             </div>
            </div>
           </div>
          </div>
          <div class="flex items-center flex-wrap gap-5 lg:gap-11">
           <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              30
             </span>
             <span class="text-secondary-foreground text-xs">
              Purchases
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $150
             </span>
             <span class="text-secondary-foreground text-xs">
              Avg. Price
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $4,500
             </span>
             <span class="text-secondary-foreground text-xs">
              Total spent
             </span>
            </div>
           </div>
           <div>
            <div class="flex -space-x-2">
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-29.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-33.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-23.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-31.png') }}"/>
             </div>
            </div>
           </div>
           <div class="text-right w-28">
            <a class="kt-btn kt-btn-outline">
             <i class="ki-filled ki-check-circle">
             </i>
             Connected
            </a>
           </div>
          </div>
         </div>
        </div>
        <div class="kt-card p-7.5">
         <div class="flex items-center flex-wrap justify-between gap-5">
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            <div class="size-20 relative">
             <img class="rounded-full" src="assets/media/avatars/300-9.png') }}"/>
             <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
              Brian Davis
             </a>
             <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
              </path>
             </svg>
            </div>
            <div class="flex items-center flex-wrap gap-x-4">
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
              </i>
              Vortex Tech
             </div>
             <div class="flex items-center text-sm">
              <i class="ki-filled ki-sms me-1 text-muted-foreground">
              </i>
              <a class="text-secondary-foreground hover:text-primary" href="#">
               brian@vortextech.com
              </a>
             </div>
            </div>
           </div>
          </div>
          <div class="flex items-center flex-wrap gap-5 lg:gap-11">
           <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              87
             </span>
             <span class="text-secondary-foreground text-xs">
              Purchases
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $22
             </span>
             <span class="text-secondary-foreground text-xs">
              Avg. Price
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $1958
             </span>
             <span class="text-secondary-foreground text-xs">
              Total spent
             </span>
            </div>
           </div>
           <div>
            <div class="flex -space-x-2">
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-14.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-3.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-19.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-15.png') }}"/>
             </div>
            </div>
           </div>
           <div class="text-right w-28">
            <a class="kt-btn kt-btn-outline">
             <i class="ki-filled ki-check-circle">
             </i>
             Connected
            </a>
           </div>
          </div>
         </div>
        </div>
        <div class="kt-card p-7.5">
         <div class="flex items-center flex-wrap justify-between gap-5">
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            <div class="flex items-center justify-center relative text-2xl text-green-500 size-20 ring-1 ring-green-200 dark:ring-green-950 bg-green-50 dark:bg-green-950/30 rounded-full">
             M
             <div class="flex size-2.5 bg-violet-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
              Megan Taylor
             </a>
             <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
              </path>
             </svg>
            </div>
            <div class="flex items-center flex-wrap gap-x-4">
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
              </i>
              Catalyst
             </div>
             <div class="flex items-center text-sm">
              <i class="ki-filled ki-sms me-1 text-muted-foreground">
              </i>
              <a class="text-secondary-foreground hover:text-primary" href="#">
               megan@catalyst.com
              </a>
             </div>
            </div>
           </div>
          </div>
          <div class="flex items-center flex-wrap gap-5 lg:gap-11">
           <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              45
             </span>
             <span class="text-secondary-foreground text-xs">
              Purchases
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $300
             </span>
             <span class="text-secondary-foreground text-xs">
              Avg. Price
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $13,500
             </span>
             <span class="text-secondary-foreground text-xs">
              Total spent
             </span>
            </div>
           </div>
           <div>
            <div class="flex -space-x-2">
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-26.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-6.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-1.png') }}"/>
             </div>
            </div>
           </div>
           <div class="text-right w-28">
            <a class="kt-btn kt-btn-primary">
             <i class="ki-filled ki-users">
             </i>
             Connect
            </a>
           </div>
          </div>
         </div>
        </div>
        <div class="kt-card p-7.5">
         <div class="flex items-center flex-wrap justify-between gap-5">
          <div class="flex items-center gap-3.5">
           <div class="flex justify-center">
            <div class="size-20 relative">
             <img class="rounded-full" src="assets/media/avatars/300-8.png') }}"/>
             <div class="flex size-2.5 bg-green-500 rounded-full absolute bottom-0.5 start-16 transform -translate-y-1/2">
             </div>
            </div>
           </div>
           <div class="grid">
            <div class="flex items-center gap-1.5 mb-2.5">
             <a class="text-base leading-5 font-medium hover:text-primary text-mono cursor-pointer" data-kt-modal-toggle="#modal_profile">
              Alex Martinez
             </a>
             <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
              <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
              </path>
             </svg>
            </div>
            <div class="flex items-center flex-wrap gap-x-4">
             <div class="flex items-center text-sm text-secondary-foreground">
              <i class="ki-filled ki-abstract-41 me-1 text-muted-foreground">
              </i>
              Precision Solutions
             </div>
             <div class="flex items-center text-sm">
              <i class="ki-filled ki-sms me-1 text-muted-foreground">
              </i>
              <a class="text-secondary-foreground hover:text-primary" href="#">
               alex@kteam.com
              </a>
             </div>
            </div>
           </div>
          </div>
          <div class="flex items-center flex-wrap gap-5 lg:gap-11">
           <div class="flex items-center flex-wrap gap-2 lg:gap-5">
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              63
             </span>
             <span class="text-secondary-foreground text-xs">
              Purchases
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $65
             </span>
             <span class="text-secondary-foreground text-xs">
              Avg. Price
             </span>
            </div>
            <div class="grid grid-cols-1 gap-1.5 border-[0.5px] border-dashed border-input shrink-0 rounded-md px-2.5 py-2">
             <span class="text-mono text-sm leading-none font-medium">
              $4,095
             </span>
             <span class="text-secondary-foreground text-xs">
              Total spent
             </span>
            </div>
           </div>
           <div>
            <div class="flex -space-x-2">
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-4.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-5.png') }}"/>
             </div>
             <div class="flex">
              <img class="hover:z-5 relative shrink-0 rounded-full ring-1 ring-background size-7" src="{{ asset('assets/media/avatars/300-11.png') }}"/>
             </div>
             <div class="flex">
              <span class="relative inline-flex items-center justify-center shrink-0 rounded-full ring-1 font-semibold leading-none text-2xs size-7 text-white ring-background bg-green-500">
               +10
              </span>
             </div>
            </div>
           </div>
           <div class="text-right w-28">
            <a class="kt-btn kt-btn-outline">
             <i class="ki-filled ki-check-circle">
             </i>
             Connected
            </a>
           </div>
          </div>
         </div>
        </div>
       </div>
       <div class="flex grow justify-center pt-5 lg:pt-7.5">
        <a class="kt-link kt-link-underlined kt-link-dashed" href="html/demo2/account/members/team-members.html">
         Show more Users
        </a>
       </div>
      </div>
     </div>
    </div>
    <!-- End of Container -->
   </main>

   <!-- Modal de Profil Utilisateur -->
   <div class="kt-modal" data-kt-modal="true" data-kt-modal-disable-scroll="false" id="modal_profile">
      <div class="kt-modal-content kt-container-fixed p-0" id="modal_profile_content">
       <div class="kt-modal-header rounded-t-lg p-0 border-0 relative min-h-80 flex flex-col items-stretch justify-end bg-center bg-cover bg-no-repeat mb-7 modal-bg">
        <div class="flex flex-col justify-end border-b-0 grow px-9 bg-linear-to-t from-light from-3% to-transparent">
         <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-outline absolute top-0 end-0 me-5 mt-5 lg:me-10 shadow-default" data-kt-modal-dismiss="true">
          <i class="ki-filled ki-cross"></i>
         </button>
         <div class="flex justify-center mb-5">
          <img class="rounded-full border-3 border-green-500 max-h-[100px]" src="{{ asset('assets/media/avatars/300-1.png') }}">
          </img>
         </div>
         <div class="grid lg:grid-cols-3 gap-3 w-full items-center">
          <div>
          </div>
          <div class="flex items-center flex-col">
           <div class="flex items-center gap-1.5 mb-2">
            <a class="text-lg leading-5 font-semibold text-foreground hover:text-primary" href="#">
             Jenny Klabbe
            </a>
            <svg class="text-primary" fill="none" height="16" viewbox="0 0 15 16" width="15" xmlns="http://www.w3.org/2000/svg">
             <path d="M14.5425 6.89749L13.5 5.83999C13.4273 5.76877 13.3699 5.6835 13.3312 5.58937C13.2925 5.49525 13.2734 5.39424 13.275 5.29249V3.79249C13.274 3.58699 13.2324 3.38371 13.1527 3.19432C13.0729 3.00494 12.9565 2.83318 12.8101 2.68892C12.6638 2.54466 12.4904 2.43073 12.2998 2.35369C12.1093 2.27665 11.9055 2.23801 11.7 2.23999H10.2C10.0982 2.24159 9.99722 2.22247 9.9031 2.18378C9.80898 2.1451 9.72371 2.08767 9.65249 2.01499L8.60249 0.957487C8.30998 0.665289 7.91344 0.50116 7.49999 0.50116C7.08654 0.50116 6.68999 0.665289 6.39749 0.957487L5.33999 1.99999C5.26876 2.07267 5.1835 2.1301 5.08937 2.16879C4.99525 2.20747 4.89424 2.22659 4.79249 2.22499H3.29249C3.08699 2.22597 2.88371 2.26754 2.69432 2.34731C2.50494 2.42709 2.33318 2.54349 2.18892 2.68985C2.04466 2.8362 1.93073 3.00961 1.85369 3.20013C1.77665 3.39064 1.73801 3.5945 1.73999 3.79999V5.29999C1.74159 5.40174 1.72247 5.50275 1.68378 5.59687C1.6451 5.691 1.58767 5.77627 1.51499 5.84749L0.457487 6.89749C0.165289 7.19 0.00115967 7.58654 0.00115967 7.99999C0.00115967 8.41344 0.165289 8.80998 0.457487 9.10249L1.49999 10.16C1.57267 10.2312 1.6301 10.3165 1.66878 10.4106C1.70747 10.5047 1.72659 10.6057 1.72499 10.7075V12.2075C1.72597 12.413 1.76754 12.6163 1.84731 12.8056C1.92709 12.995 2.04349 13.1668 2.18985 13.3111C2.3362 13.4553 2.50961 13.5692 2.70013 13.6463C2.89064 13.7233 3.0945 13.762 3.29999 13.76H4.79999C4.90174 13.7584 5.00275 13.7775 5.09687 13.8162C5.191 13.8549 5.27627 13.9123 5.34749 13.985L6.40499 15.0425C6.69749 15.3347 7.09404 15.4988 7.50749 15.4988C7.92094 15.4988 8.31748 15.3347 8.60999 15.0425L9.65999 14C9.73121 13.9273 9.81647 13.8699 9.9106 13.8312C10.0047 13.7925 10.1057 13.7734 10.2075 13.775H11.7075C12.1212 13.775 12.518 13.6106 12.8106 13.3181C13.1031 13.0255 13.2675 12.6287 13.2675 12.215V10.715C13.2659 10.6132 13.285 10.5122 13.3237 10.4181C13.3624 10.324 13.4198 10.2387 13.4925 10.1675L14.55 9.10999C14.6953 8.96452 14.8104 8.79176 14.8887 8.60164C14.9671 8.41152 15.007 8.20779 15.0063 8.00218C15.0056 7.79656 14.9643 7.59311 14.8847 7.40353C14.8051 7.21394 14.6888 7.04197 14.5425 6.89749ZM10.635 6.64999L6.95249 10.25C6.90055 10.3026 6.83864 10.3443 6.77038 10.3726C6.70212 10.4009 6.62889 10.4153 6.55499 10.415C6.48062 10.4139 6.40719 10.3982 6.33896 10.3685C6.27073 10.3389 6.20905 10.2961 6.15749 10.2425L4.37999 8.44249C4.32532 8.39044 4.28169 8.32793 4.25169 8.25867C4.22169 8.18941 4.20593 8.11482 4.20536 8.03934C4.20479 7.96387 4.21941 7.88905 4.24836 7.81934C4.27731 7.74964 4.31999 7.68647 4.37387 7.63361C4.42774 7.58074 4.4917 7.53926 4.56194 7.51163C4.63218 7.484 4.70726 7.47079 4.78271 7.47278C4.85816 7.47478 4.93244 7.49194 5.00112 7.52324C5.0698 7.55454 5.13148 7.59935 5.18249 7.65499L6.56249 9.05749L9.84749 5.84749C9.95296 5.74215 10.0959 5.68298 10.245 5.68298C10.394 5.68298 10.537 5.74215 10.6425 5.84749C10.6953 5.90034 10.737 5.96318 10.7653 6.03234C10.7935 6.1015 10.8077 6.1756 10.807 6.25031C10.8063 6.32502 10.7908 6.39884 10.7612 6.46746C10.7317 6.53608 10.6888 6.59813 10.635 6.64999Z" fill="currentColor">
             </path>
            </svg>
           </div>
           <div class="flex flex-wrap justify-center gap-1 lg:gap-3 text-sm">
            <div class="flex gap-1 items-center">
             <i class="ki-filled ki-abstract text-muted-foreground text-base"></i>
             <a class="text-secondary-foreground hover:text-primary" href="https://keenthemes.com">
              Keenthemes
             </a>
            </div>
            <div class="flex gap-1 items-center">
             <i class="ki-filled ki-sms text-muted-foreground text-base"></i>
             <a class="text-secondary-foreground hover:text-primary" href="mailto:jenny@kteam.com">
              jenny@kteam.com
             </a>
            </div>
           </div>
          </div>
          <div class="flex justify-end gap-2">
           <button class="kt-btn kt-btn-primary">
            <i class="ki-filled ki-users"></i>
            Connect
           </button>
           <button class="kt-btn kt-btn-icon kt-btn-outline">
            <i class="ki-filled ki-messages"></i>
           </button>
           <div data-kt-dropdown="true" data-kt-dropdown-placement="bottom-end" data-kt-dropdown-placement-rtl="bottom-start" data-kt-dropdown-trigger="click">
            <button class="kt-btn kt-btn-icon kt-btn-outline" data-kt-dropdown-toggle="true">
             <i class="ki-filled ki-dots-vertical"></i>
            </button>
            <div class="kt-dropdown-menu w-full max-w-[220px]" data-kt-dropdown-menu="true">
             <ul class="kt-dropdown-menu-sub">
              <li>
               <button class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true">
                <i class="ki-filled ki-coffee"></i>
                Share Profile
               </button>
              </li>
              <li>
               <button class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true">
                <i class="ki-filled ki-award"></i>
                Give Award
               </button>
              </li>
              <li>
               <div class="kt-dropdown-menu-link">
                <i class="ki-filled ki-coffee"></i>
                Stay Updated
                <input class="ms-auto kt-switch kt-switch-sm" name="check" type="checkbox" value="1"/>
               </div>
              </li>
              <li>
               <button class="kt-dropdown-menu-link" data-kt-dropdown-dismiss="true">
                <i class="ki-filled ki-information-2"></i>
                Report User
               </button>
              </li>
             </ul>
            </div>
           </div>
          </div>
         </div>
        </div>
       </div>
       <div class="kt-modal-body kt-scrollable-y py-0 mb-5 ps-6 pr-3 me-3">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-5 lg:gap-7.5">
         <div class="col-span-1">
          <div class="grid gap-5 lg:gap-7.5">
           <div class="kt-card">
            <div class="kt-card-header">
             <h3 class="kt-card-title">
              Community Badges
             </h3>
            </div>
            <div class="kt-card-content pb-7.5">
             <div class="flex items-center flex-wrap gap-3 lg:gap-4">
              <div class="relative size-[50px] shrink-0">
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
                <i class="ki-filled ki-abstract-39 text-xl ps-px text-primary"></i>
               </div>
              </div>
              <div class="relative size-[50px] shrink-0">
               <svg class="w-full h-full stroke-yellow-200 dark:stroke-yellow-950 fill-yellow-100 dark:fill-yellow-950/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
                <i class="ki-filled ki-abstract-44 text-xl ps-px text-yellow-600"></i>
               </div>
              </div>
              <div class="relative size-[50px] shrink-0">
               <svg class="w-full h-full stroke-green-200 dark:stroke-green-950 fill-green-100 dark:fill-green-950/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
                <i class="ki-filled ki-abstract-25 text-xl ps-px text-green-600"></i>
               </div>
              </div>
              <div class="relative size-[50px] shrink-0">
               <svg class="w-full h-full stroke-violet-200 dark:stroke-violet-950 fill-violet-100 dark:fill-violet-950/30" fill="none" height="48" viewbox="0 0 44 48" width="44" xmlns="http://www.w3.org/2000/svg">
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
                <i class="ki-filled ki-delivery-24 text-xl ps-px text-violet-600"></i>
               </div>
              </div>
             </div>
            </div>
           </div>
           <div class="kt-card">
            <div class="kt-card-header">
             <h3 class="kt-card-title">
              About
             </h3>
            </div>
            <div class="kt-card-content pt-4 pb-3">
             <table class="kt-table-auto">
              <tbody>
               <tr>
                <td class="text-sm text-secondary-foreground pb-3.5 pe-3">
                 Age
                </td>
                <td class="text-sm text-mono pb-3.5">
                 32
                </td>
               </tr>
               <tr>
                <td class="text-sm text-secondary-foreground pb-3.5 pe-3">
                 City:
                </td>
                <td class="text-sm text-mono pb-3.5">
                 Amsterdam
                </td>
               </tr>
               <tr>
                <td class="text-sm text-secondary-foreground pb-3.5 pe-3">
                 State:
                </td>
                <td class="text-sm text-mono pb-3.5">
                 North Holland
                </td>
               </tr>
               <tr>
                <td class="text-sm text-secondary-foreground pb-3.5 pe-3">
                 Country:
                </td>
                <td class="text-sm text-mono pb-3.5">
                 Netherlands
                </td>
               </tr>
               <tr>
                <td class="text-sm text-secondary-foreground pb-3.5 pe-3">
                 Postcode:
                </td>
                <td class="text-sm text-mono pb-3.5">
                 1092 NL
                </td>
               </tr>
               <tr>
                <td class="text-sm text-secondary-foreground pb-3.5 pe-3">
                 Phone:
                </td>
                <td class="text-sm text-mono pb-3.5">
                 +31 6 1234 56 78
                </td>
               </tr>
               <tr>
                <td class="text-sm text-secondary-foreground pb-3.5 pe-3">
                 Email:
                </td>
                <td class="text-sm text-mono pb-3.5">
                 <a class="text-foreground hover:text-primary" href="#">
                  jenny@ktstudio.com
                 </a>
                </td>
               </tr>
              </tbody>
             </table>
            </div>
           </div>
           <div class="kt-card">
            <div class="kt-card-header">
             <h3 class="kt-card-title">
              Work Experience
             </h3>
            </div>
            <div class="kt-card-content">
             <div class="grid gap-y-5">
              <div class="flex align-start gap-3.5">
               <img alt="" class="h-9" src="{{ asset('assets/media/brand-logos/jira.svg') }}"/>
               <div class="flex flex-col gap-1">
                <a class="text-sm font-medium text-primary leading-none hover:text-primary" href="#">
                 Esprito Studios
                </a>
                <span class="text-sm font-medium text-mono">
                 Senior Project Manager
                </span>
                <span class="text-xs text-secondary-foreground leading-none">
                 2019 - Present
                </span>
               </div>
              </div>
              <div class="text-secondary-foreground font-semibold text-sm leading-none">
               Previous Jobs
              </div>
              <div class="flex align-start gap-3.5">
               <img alt="" class="h-9" src="{{ asset('assets/media/brand-logos/weave.svg') }}"/>
               <div class="flex flex-col gap-1">
                <a class="text-sm font-medium text-primary leading-none hover:text-primary" href="#">
                 Pesto Plus
                </a>
                <span class="text-sm font-medium text-mono">
                 CRM Product Lead
                </span>
                <span class="text-xs text-secondary-foreground leading-none">
                 2012 - 2019
                </span>
               </div>
              </div>
              <div class="flex align-start gap-3.5">
               <img alt="" class="h-9" src="{{ asset('assets/media/brand-logos/perrier.svg') }}"/>
               <div class="flex flex-col gap-1">
                <a class="text-sm font-medium text-primary leading-none hover:text-primary" href="#">
                 Perrier Technologies
                </a>
                <span class="text-sm font-medium text-mono">
                 UX Research
                </span>
                <span class="text-xs text-secondary-foreground leading-none">
                 2010 - 2012
                </span>
               </div>
              </div>
             </div>
            </div>
            <div class="kt-card-footer justify-center">
             <a class="kt-link kt-link-underlined kt-link-dashed" href="#">
              Open to Work
             </a>
            </div>
           </div>
          </div>
         </div>
         <div class="col-span-2">
          <div class="flex flex-col gap-5 lg:gap-7.5">
           <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 lg:gap-7.5">
            <div class="kt-card">
             <div class="kt-card-header gap-2">
              <h3 class="kt-card-title">
               Contributors
              </h3>
             </div>
             <div class="kt-card-content">
              <div class="flex flex-col gap-2 lg:gap-5">
               <div class="flex items-center gap-2">
                <div class="flex items-center grow gap-2.5">
                 <img alt="" class="rounded-full size-9 shrink-0" src="{{ asset('assets/media/avatars/300-3.png') }}"/>
                 <div class="flex flex-col">
                  <a class="text-sm font-medium text-mono hover:text-primary mb-px" href="#">
                   Tyler Hero
                  </a>
                  <span class="text-xs text-secondary-foreground">
                   6 connections
                  </span>
                 </div>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-outline rounded-full">
                 <i class="ki-filled ki-plus"></i>
                </button>
               </div>
               <div class="flex items-center gap-2">
                <div class="flex items-center grow gap-2.5">
                 <img alt="" class="rounded-full size-9 shrink-0" src="{{ asset('assets/media/avatars/300-1.png') }}"/>
                 <div class="flex flex-col">
                  <a class="text-sm font-medium text-mono hover:text-primary mb-px" href="#">
                   Esther Howard
                  </a>
                  <span class="text-xs text-secondary-foreground">
                   29 connections
                  </span>
                 </div>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-outline rounded-full active">
                 <i class="ki-filled ki-check"></i>
                </button>
               </div>
               <div class="flex items-center gap-2">
                <div class="flex items-center grow gap-2.5">
                 <img alt="" class="rounded-full size-9 shrink-0" src="{{ asset('assets/media/avatars/300-14.png') }}"/>
                 <div class="flex flex-col">
                  <a class="text-sm font-medium text-mono hover:text-primary mb-px" href="#">
                   Cody Fisher
                  </a>
                  <span class="text-xs text-secondary-foreground">
                   34 connections
                  </span>
                 </div>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-outline rounded-full">
                 <i class="ki-filled ki-plus"></i>
                </button>
               </div>
               <div class="flex items-center gap-2">
                <div class="flex items-center grow gap-2.5">
                 <img alt="" class="rounded-full size-9 shrink-0" src="{{ asset('assets/media/avatars/300-7.png') }}"/>
                 <div class="flex flex-col">
                  <a class="text-sm font-medium text-mono hover:text-primary mb-px" href="#">
                   Arlene McCoy
                  </a>
                  <span class="text-xs text-secondary-foreground">
                   1 connections
                  </span>
                 </div>
                </div>
                <button class="kt-btn kt-btn-sm kt-btn-icon kt-btn-primary kt-btn-outline rounded-full active">
                 <i class="ki-filled ki-check"></i>
                </button>
               </div>
              </div>
             </div>
             <div class="kt-card-footer justify-center">
              <a class="kt-link kt-link-underlined kt-link-dashed" href="#">
               All Contributors
              </a>
             </div>
            </div>
            <div class="kt-card">
             <div class="kt-card-header">
              <h3 class="kt-card-title">
               Assistance
              </h3>
             </div>
             <div class="kt-card-content flex justify-center items-center px-3 py-1">
              <div id="contributions_chart">
              </div>
             </div>
            </div>
           </div>
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
   <!-- End Modal de Profil Utilisateur -->
@endsection