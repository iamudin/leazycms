<!doctype html>
<html lang="en">
    <head>
        <title>{{ !session('dbcredential')  ? 'Database Credential > ':'User & Site > '}}Setup</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    </head>

    <body>

        <main>
           <div class="container mt-2 mb-2">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center bg-warning">
                            <h3> <i class="bi bi-gear"></i> CMS Setup</h3>
                        </div>
                        <div class="card-body">
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                            @if(session('danger'))
                            <div class="alert alert-danger">
                                {{ session('danger') }}
                            </div>
                            @endif

                            <form action="{{ url()->current() }}" method="POST">
                                @csrf
                                @if(!cache('dbcredential'))
                                <div class="alert alert-info">
                                    <h1>Welcome to CMS laravel !</h1> Please finish installation step bellow for ready use this System. Good Luck!
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <i class="bi bi-database"></i> Database Credential
                                    </div>
                                    <div class="card-body">

                                <div class="mb-3">
                                    <label for="dbHost" class="form-label">Database Host</label>
                                    <input required type="text" name="db_host" class="form-control" value="{{ env('DB_HOST','127.0.0.1') }}" id="dbHost" placeholder="Enter database host">
                                </div>
                                <div class="mb-3">
                                    <label for="dbUser" class="form-label">Database Username</label>
                                    <input  required type="text" name="db_username" class="form-control" id="dbUser" value="{{ env('DB_USERNAME','root') }}"  placeholder="Enter database username">
                                </div>
                                <div class="mb-3">
                                    <label for="dbPassword" class="form-label">Database Password</label>
                                    <input  type="password" name="db_password" class="form-control" value="{{ env('DB_PASSWORD',null) }}" id="dbPassword" placeholder="Enter database password">
                                </div>

                                <div class="mb-3">
                                    <label for="dbName" class="form-label">Database Name</label>
                                    <input required type="text" name="db_database" class="form-control" value="{{ env('DB_DATABASE') }}" id="dbName" placeholder="Enter database name">
                                </div>
                                </div>
                            </div>
                                @else
                                @if(cache('dbcredential'))
                                <div class="alert alert-info">
                                    Your CMS will installed on :<br>
                                    Domain : <b>{{ request()->getHttpHost() }}</b><br>
                                    DB Name : <b>{{ cache('dbcredential')['db_database'] }}</b><br>
                                    DB Host: <b>{{ cache('dbcredential')['db_host'] }}</b><br>
                                    DB Username : <b>{{ cache('dbcredential')['db_username'] }}</b><br>
                                    DB Password : <b>*******</b><br>
                                </div>
                                @endif
                                <div class="card">
                                    <div class="card-header">
                                        <i class="bi bi-globe"></i> Site
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="adminEmail" class="form-label">Site Title</label>
                                            <input value="{{ old('site_title') ?? null }}"  type="text" name="site_title" class="form-control"  placeholder="Enter Site Title">
                                        </div>
                                        <div class="mb-3">
                                            <label for="adminEmail" class="form-label">Site Description</label>
                                            <input value="{{ old('site_description') ?? null }}" type="text" name="site_description" class="form-control"placeholder="Enter Site Description">
                                        </div>
                                    </div>
                                </div>
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <i class="bi bi-person-lock"></i> Admin Credential
                                    </div>
                                    <div class="card-body">
                                <div class="mb-3">
                                    <label for="adminEmail" class="form-label">Admin Email</label>
                                    <input type="email" name="email" class="form-control" id="adminEmail" placeholder="Enter admin email">
                                </div>
                                <div class="mb-3">
                                    <label for="adminUser" class="form-label">Admin Username</label>
                                    <input type="text" name="username" class="form-control" id="adminUser" placeholder="Enter admin username">
                                </div>
                                <div class="mb-3">
                                    <label for="adminPassword" class="form-label">Admin Password</label>
                                    <input type="password" name="password" class="form-control" id="adminPassword" placeholder="Enter admin password">
                                </div>
                                <div class="mb-3">
                                    <label for="adminPassword" class="form-label">Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" id="adminPassword" placeholder="Enter Confirm admin password">
                                </div>
                            </div>
                        </div>

                                @endif

                                <div class="d-grid mt-3">
                                    <button type="submit" class="btn btn-info">@if(cache('dbcredential')) Install Now @else Next @endif <i class="bi bi-arrow-right"></i></button>
                                </div>
                            </form>
                        </div>
                        {{-- <div class="card-footer text-center">
                            <small>&copy; Laradesk.id</small>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>
        </main>

        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
