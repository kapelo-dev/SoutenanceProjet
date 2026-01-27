@extends('layouts.demo1.base')

@section('content')
      <!-- Container -->
      <div class="kt-container-fixed">
      <div class="flex flex-wrap items-center lg:items-end justify-between gap-5 pb-7.5">
       <div class="flex flex-col justify-center gap-2">
        <h1 class="text-xl font-medium leading-none text-mono">
         permissions
        </h1>
        <div class="flex items-center gap-2 text-sm font-normal text-secondary-foreground">
         Overview of all team members and permissions.
        </div>
       </div>
       <div class="flex items-center gap-2.5">
        <a class="kt-btn kt-btn-outline" href="#">
         New permissions
        </a>
       </div>
      </div>
     </div>
     <!-- End of Container -->
     <div class="kt-container-fixed">
      <!-- begin: grid -->
      <div class="grid grid-cols-1 xl:grid-cols-1 gap-5 lg:gap-7.5">
       <div class="col-span-2">
        <div class="flex flex-col gap-5 lg:gap-7.5">
         <div class="kt-card">
          <div class="kt-card-header gap-2">
           <h3 class="kt-card-title">
            <a class="text-primary" href="#">
             Project Manager
            </a>
            Role Permissions
           </h3>
           <div class="flex gap-5">
            <a class="kt-btn kt-btn-outline shrink-0" href="#">
             New Permission
            </a>
           </div>
          </div>
          <div class="kt-card-table kt-scrollable-x-auto">
           <table class="kt-table">
            <thead>
             <tr>
              <th class="text-left text-muted-foreground font-normal min-w-[300px]">
               Module
              </th>
              <th class="min-w-24 text-secondary-foreground font-normal text-center">
               View
              </th>
              <th class="min-w-24 text-secondary-foreground font-normal text-center">
               Modify
              </th>
              <th class="min-w-24 text-secondary-foreground font-normal text-center">
               Publish
              </th>
              <th class="min-w-24 text-secondary-foreground font-normal text-center">
               Configure
              </th>
             </tr>
            </thead>
            <tbody class="text-mono font-medium">
             <tr>
              <td class="py-5.5!">
               Workspace Settings
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               Billing Management
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               Integration Setup
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               Map Creation
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               Data Export
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               User Roles
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               Security Settings
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               Insights Access
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
             <tr>
              <td class="py-5.5!">
               Merchant List
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input checked="" class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
              <td class="py-5.5! text-center">
               <input class="kt-checkbox kt-checkbox-sm" name="" type="checkbox" value=""/>
              </td>
             </tr>
            </tbody>
           </table>
          </div>
          <div class="kt-card-footer justify-end py-7.5 gap-2.5">
           <a class="kt-btn kt-btn-outline" href="#">
            Restore Defaults
           </a>
           <a class="kt-btn kt-btn-primary" href="#">
            Save Changes
           </a>
          </div>
         </div>
       </div>
      </div>
      <!-- end: grid -->
     </div>
@endsection
