<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Route List Web</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha256-fx038NkLY4U1TCrBDiu5FWPEa9eiZu01EiLryshJbCo=" crossorigin="anonymous">
    <link href="https://fonts.bunny.net/css2?family=Roboto+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        [x-cloak] {
            display: none !important;
        }

        .font-monospace {
            font-family: 'Roboto Mono', var(--bs-font-monospace) !important;
        }

        .fw-medium {
            font-weight: 500;
        }

        .text-indigo {
            color: var(--bs-indigo);
        }

        .text-orange {
            color: var(--bs-orange);
        }

        .bg-teal {
            background-color: var(--bs-teal);
        }

        .bg-orange {
            background-color: var(--bs-orange);
        }

        .form-check-label {
            cursor: pointer;
            margin-left: -0.5rem;
            padding-left: 0.5rem;
        }

        .theme-toggle {
            background-color: transparent;
            border-radius: 50%;
            border: 2px solid #ffa600;
            color: #ffa600;
            line-height: 0;
            padding: 0.3rem;
        }

        .scheme-toggle:hover {
            background-color: #ffa600;
            color: #fff;
        }

        [data-bs-theme=dark] .theme-toggle {
            border-color: #ff82ff;
            color: #ff82ff;
        }

        [data-bs-theme=dark] .theme-toggle:hover {
            background-color: #ff82ff;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="container-fluid" x-data="RouteList()">
        <div class="row mt-3 mb-2">
            <div class="col-md-2">
                <select class="form-select" x-model="search.column">
                        <option value="all">All</option>
                    @foreach ($columns as $column => $label)
                        <option value="{{ $column }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-9 mt-1 mt-md-0">
                <input type="text" class="form-control" placeholder="Search here..." x-model.debounce.300="search.value" x-on:keydown.escape="search.value = ''">
            </div>
            <div class="col-md-1 d-grid mt-2 mt-md-0">
                <button type="button" class="btn btn-primary" x-on:click="refresh" x-bind:disabled="refreshing">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-9">
                @foreach ($columns as $column => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="columns.{{ $column }}" x-model="columns.{{ $column }}">
                        <label class="form-check-label" for="columns.{{ $column }}">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="col-md-3">
                <div class="fw-bold text-end">
                    <div class="d-inline-block me-1"><span x-show="filteredCount !== routes.length" x-cloak><span x-text="filteredCount"></span> of</span> <span x-text="total">{{ $routes->count() }}</span> routes</div>

                    <button type="button" class="theme-toggle" x-on:click="darkMode = !darkMode">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sun-fill" viewBox="0 0 16 16" x-show="!darkMode" x-cloak>
                            <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-moon-fill" viewBox="0 0 16 16" x-show="darkMode" x-cloak>
                            <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive" x-show="loaded" x-cloak>
            <table class="table table-sm table-hover">
                <thead>
                    <tr class="table-dark">
                        <th x-show="columns.method">Method</th>
                        <th x-show="columns.uri || columns.name" style="width: 120px">
                            <span x-show="columns.uri">URI</span>
                            <span x-show="columns.uri && columns.name">&amp;</span>
                            <span x-show="columns.name">Name</span>
                        </th>
                        <th x-show="columns.action || columns.middleware">
                            <span x-show="columns.action">Action</span>
                            <span x-show="columns.action && columns.middleware">&amp;</span>
                            <span x-show="columns.middleware">Middleware</span>
                        </th>
                    </tr>
                </thead>
                <tbody style="font-size: 90%">
                    <template x-for="(route, index) in getRoutes()" x-bind:key="index">
                        <tr>
                            <td x-show="columns.method" style="width: 120px">
                                <template x-for="method in route.method">
                                    <span>
                                        <span class="badge py-1" x-bind:class="getMethodColor(method)" x-text="method"></span>
                                    </span>
                                </template>
                            </td>
                            <td class="font-monospace" x-show="columns.uri || columns.name">
                                <div class="fw-medium text-nowrap" x-html="stylizeUri(route.domain, route.uri)" x-show="columns.uri"></div>
                                <div x-text="route.name" x-show="columns.name"></div>
                            </td>
                            <td class="font-monospace" x-show="columns.action || columns.middleware">
                                <div class="fw-medium" x-html="stylizeAction(route.action)" x-show="columns.action"></div>
                                <div x-show="columns.middleware">
                                    <div class="small">
                                        <template x-for="item in route.middleware">
                                            <div x-text="'- ' + item"></div>
                                        </template>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha256-qlPVgvl+tZTCpcxYJFdHB/m6mDe84wRr+l81VoYPTgQ=" crossorigin="anonymous"></script>
    <script>
        window.RouteList = function () {
            return {
                routes: [],
                total: @json($routes->count()),
                filteredCount: @json($routes->count()),
                loaded: false,
                refreshing: false,
                darkMode: Alpine.$persist(false).as('rlw_darkMode'),

                search: Alpine.$persist({
                    column: 'all',
                    value: '',
                }).as('rlw_search'),

                columns: Alpine.$persist({
                    @foreach ($columns as $column => $label)
                        {{ $column }}: true,
                    @endforeach
                }).as('rlw_columns'),

                init: function () {
                    const that = this;

                    function enableDarkMode(value) {
                        document.documentElement.dataset.bsTheme = value ? 'dark' : 'light';
                    }

                    enableDarkMode(that.darkMode);

                    that.$watch('darkMode', function (value) {
                        enableDarkMode(value);
                    });

                    that.$nextTick(function () {
                        that.routes = @json($routes);
                        that.loaded = true;
                    });
                },

                refresh: function () {
                    const that = this;
                    that.refreshing = true;

                    fetch(@json(route('route:list')), {
                        headers: {
                            'Accept': 'application/json',
                        },
                    }).then(function (response) {
                        return response.json();
                    }).then(function (routes) {
                        that.routes = routes;
                        that.total = routes.length;
                        that.refreshing = false;
                    });
                },

                getRoutes: function () {
                    let routes = Array.from(this.routes);

                    if (this.search.value.trim().length > 0) {
                        let filteredRoutes = [];

                        for (let i = 0; i < routes.length; i++) {
                            const route = routes[i];
                            let value;

                            if (this.search.column === 'all') {
                                value = JSON.stringify(Object.values(route));
                            } else  if (this.search.column === 'method' || this.search.column === 'middleware') {
                                value = JSON.stringify(route[this.search.column]);
                            } else {
                                value = route[this.search.column];
                            }

                            if (value !== null && value.length > 0 && value.toLowerCase().includes(this.search.value.trim().toLowerCase())) {
                                filteredRoutes.push(route);
                            }
                        }

                        routes = filteredRoutes;
                    }

                    this.filteredCount = routes.length;

                    return routes;
                },

                getMethodColor: function (method) {
                    return 'bg-' + ({
                        get: 'teal',
                        head: 'secondary',
                        options: 'info',
                        post: 'primary',
                        put: 'orange',
                        patch: 'orange',
                        delete: 'danger',
                        any: 'dark',
                    })[method.toLowerCase()];
                },

                stylizeUri: function (domain, uri) {
                    uri = domain ? domain + '/' + uri : uri;
                    uri = uri.replace(/\/$/, '');
                    uri = uri.replaceAll('{', '<span class="text-orange">{').replaceAll('}', '}</span>');

                    return uri;
                },

                stylizeAction: function (action) {
                    if (action.includes('@')) {
                        action = action.replace('@', '<span class="text-primary">@') + '</span>';
                    }

                    return action;
                }
            };
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.12.2/dist/cdn.min.js" integrity="sha256-rdzBMVaKvHqpoplwGSKTvgS3dVI+gjaITQt1IlMNilM=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.12.2/dist/cdn.min.js" integrity="sha256-GfXWhneas88pmSLgCCcwxXZXIAbz7BYYh/uPV1m+ozA=" crossorigin="anonymous"></script>
</body>
</html>
