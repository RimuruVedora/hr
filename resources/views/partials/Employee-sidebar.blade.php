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
          <a class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}"><ion-icon name="home-outline"></ion-icon>Dashboard</a>
          
          <!-- Employee Sections -->
          <div class="dropdown">
            <button type="button" class="nav-link dropdown-toggle {{ request()->routeIs('learning.*') ? 'active' : '' }}" aria-expanded="false"><ion-icon name="school-outline"></ion-icon>My Learning</button>
            <div class="dropdown-menu">
                            <a class="dropdown-item {{ request()->routeIs('learning.employee.assessments') ? 'active' : '' }}" href="{{ route('learning.employee.assessments') }}"><ion-icon name="bar-chart-outline"></ion-icon>All Courses</a>
              <a class="dropdown-item {{ request()->routeIs('employee.exams') ? 'active' : '' }}" href="{{ route('employee.exams') }}"><ion-icon name="book-outline"></ion-icon>My Exams</a>
            </div> 
          </div>

         <div class="dropdown">
            <button type="button" class="nav-link dropdown-toggle {{ request()->routeIs('training.*') ? 'active' : '' }}" aria-expanded="false"><ion-icon name="calendar-outline"></ion-icon>My Training</button>
            <div class="dropdown-menu">
              <a class="dropdown-item {{ request()->routeIs('training.schedule') ? 'active' : '' }}" href="{{ route('training.schedule') }}"><ion-icon name="layers-outline"></ion-icon>My Trainings</a>
            </div> 
          </div>
                  </nav>
      </div>

    <!-- Logout -->
    <div class="p-3 border-top mb-2">
                <a class="nav-link" href="#"><ion-icon name="bar-chart-outline"></ion-icon>Profile</a>
                          <a class="nav-link" href="#"><ion-icon name="bar-chart-outline"></ion-icon>Settings</a>
                          <a class="nav-link" href="#"><ion-icon name="bar-chart-outline"></ion-icon>Sync</a>
      <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="nav-link text-danger" style="background: none; border: none; width: 100%; text-align: left;">
          <ion-icon name="log-out-outline"></ion-icon>Logout
        </button>
      </form>
    </div>
  </div>
</div>
