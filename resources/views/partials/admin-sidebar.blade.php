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
          <a class="nav-link" href="#"><ion-icon name="newspaper-outline"></ion-icon>Reports</a>
          <a class="nav-link" href="#"><ion-icon name="document-text-outline"></ion-icon>Drivers' Response</a>
          <a class="nav-link" href="learning"><ion-icon name="library-outline"></ion-icon>Drivers' Learning</a>
          <a class="nav-link" href="training"><ion-icon name="car-sport-outline"></ion-icon>Drivers' Training</a>
          <a class="nav-link" href="#"><ion-icon name="bar-chart-outline"></ion-icon>SOP</a>
        </nav>
      </div>

    <!-- Logout -->
    <div class="p-3 border-top mb-2">
      <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="nav-link text-danger" style="background: none; border: none; width: 100%; text-align: left;">
          <ion-icon name="log-out-outline"></ion-icon>Logout
        </button>
      </form>
    </div>
  </div>
</div>
