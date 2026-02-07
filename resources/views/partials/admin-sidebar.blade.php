<!-- Sidebar Column -->
<div class="sidenav col-auto p-0">
  @vite(['resources/css/admin-sidebar/sidebar.css', 'resources/js/admin-sidebar/sidebar.js'])
  <div class="sidebar d-flex flex-column justify-content-between shadow-sm border-end">

    <!-- Top Section -->
    <div class="">
      <div class="d-flex justify-content-center align-items-center mb-5 mt-3">
        <img src="{{ asset('assets/images/logo.png') }}" class="img-fluid me-2" style="height: 100px;" alt="ViaHale Logo">
      </div>

      <!-- Main Navigation -->
      <div class="mb-4">
        <h6 class="text-uppercase mb-2">Main</h6>
        <nav class="nav flex-column">
          <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><ion-icon name="home-outline"></ion-icon>Dashboard</a>
          <a class="nav-link {{ request()->routeIs('user.management') ? 'active' : '' }}" href="{{ route('user.management') }}"><ion-icon name="people-outline"></ion-icon>User Management</a>
          <div class="dropdown">
            <button type="button" class="nav-link dropdown-toggle" aria-expanded="false"><ion-icon name="newspaper-outline"></ion-icon>Competency</button>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="{{ route('competency.framework') }}"><ion-icon name="layers-outline"></ion-icon>Competency Framework</a>
              <a class="dropdown-item" href="{{ route('competency.analytics') }}"><ion-icon name="analytics-outline"></ion-icon>Competency Analytics</a>
            </div>
          </div>
          <div class="dropdown">
            <button type="button" class="nav-link dropdown-toggle {{ request()->routeIs('learning.*') ? 'active' : '' }}" aria-expanded="false"><ion-icon name="school-outline"></ion-icon>Learning Management</button>
            <div class="dropdown-menu">
              <a class="dropdown-item {{ request()->routeIs('learning.courses') ? 'active' : '' }}" href="{{ route('learning.courses') }}"><ion-icon name="book-outline"></ion-icon>Courses</a>
              <a class="dropdown-item {{ request()->routeIs('learning.assessments') ? 'active' : '' }}" href="{{ route('learning.assessments') }}"><ion-icon name="clipboard-outline"></ion-icon>Assessments</a>
                          <a class="dropdown-item {{ request()->routeIs('learning.assessment-scores') ? 'active' : '' }}" href="{{ route('learning.assessment-scores') }}"><ion-icon name="bar-chart-outline"></ion-icon>Assessment Score</a>
            </div> 
          </div>
         <div class="dropdown">
            <button type="button" class="nav-link dropdown-toggle" aria-expanded="false"><ion-icon name="newspaper-outline"></ion-icon>Training Management</button>
            <div class="dropdown-menu">
              <a class="dropdown-item {{ request()->routeIs('training.schedule') ? 'active' : '' }}" href="{{ route('training.schedule') }}"><ion-icon name="layers-outline"></ion-icon>Training Schedule</a>
              <a class="dropdown-item {{ request()->routeIs('training.evaluation') ? 'active' : '' }}" href="{{ route('training.evaluation') }}"><ion-icon name="analytics-outline"></ion-icon>Evaluation</a>
            </div> 
          </div>
            <div class="dropdown">
            <button type="button" class="nav-link dropdown-toggle" aria-expanded="false"><ion-icon name="newspaper-outline"></ion-icon>Succession Planning</button>
            <div class="dropdown-menu">
              <a class="dropdown-item {{ request()->routeIs('talent.assessment') ? 'active' : '' }}" href="{{ route('talent.assessment') }}"><ion-icon name="layers-outline"></ion-icon>Talent Assessment</a>
              <a class="dropdown-item {{ request()->routeIs('succession.plans') ? 'active' : '' }}" href="{{ route('succession.plans') }}"><ion-icon name="analytics-outline"></ion-icon>Succession Plans</a>
            </div> 
          </div>
        </nav>
      </div>

    <!-- Logout -->
    <div class="p-3 border-top mb-2">
                <a class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}" href="{{ route('profile') }}"><ion-icon name="person-outline"></ion-icon>Profile</a>
                          <a class="nav-link" href="#"><ion-icon name="settings-outline"></ion-icon>Settings</a>
                          <a class="nav-link {{ request()->routeIs('sync.index') ? 'active' : '' }}" href="{{ route('sync.index') }}"><ion-icon name="sync-outline"></ion-icon>Sync</a>
      <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="nav-link text-danger" style="background: none; border: none; width: 100%; text-align: left;">
          <ion-icon name="log-out-outline"></ion-icon>Logout
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Session Timeout Warning Component -->
@include('partials.session-timeout')
