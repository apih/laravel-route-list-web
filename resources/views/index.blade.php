<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Route List Web</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha256-PI8n5gCcz9cQqQXm3PEtDuPG8qx9oFsFctPg0S5zb8g=" crossorigin="anonymous">
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
    <div class="container-fluid pb-3" x-data="RouteList()">
        <div class="d-flex flex-column flex-md-row justify-content-start gap-2 mt-3 mb-2">
            <div>
                <select class="form-select" x-model="search.column">
                        <option value="all">All</option>
                    @foreach ($columns as $column => $label)
                        <option value="{{ $column }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-fill">
                <input type="text" class="form-control" placeholder="Search here..." x-model.debounce.300="search.value" x-on:keydown.escape="search.value = ''">
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-primary" x-on:click="refresh" x-bind:disabled="refreshing">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-start align-md-items-center gap-1 mb-1">
            <div class="align-sm-self-stretch">
                <label class="col-form-label fw-bold me-2">Columns</label>
                @foreach ($columns as $column => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="columns.{{ $column }}" x-model="columns.{{ $column }}">
                        <label class="form-check-label" for="columns.{{ $column }}">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="align-sm-self-stretch ms-md-2">
                <label class="col-form-label fw-bold me-2">Options</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="options.expandMiddlewareGroup" x-model="options.expandMiddlewareGroup">
                    <label class="form-check-label" for="options.expandMiddlewareGroup">
                        Expand Middleware Group
                    </label>
                </div>
            </div>
            <div class="align-sm-self-stretch ms-auto pt-1">
                <div class="d-flex align-items-center fw-bold">
                    <span><span x-show="filteredCount !== routes.length" x-cloak><span x-text="filteredCount"></span> of</span> <span x-text="total">{{ $routes->count() }}</span> routes</span>

                    <button type="button" class="theme-toggle ms-1" x-on:click="darkMode = !darkMode">
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

        @section('pagination')
            <nav x-show="loaded" x-cloak>
                <ul class="pagination pagination-sm justify-content-center mb-2">
                    <li class="page-item" x-bind:class="page <= 1 ? 'disabled' : ''">
                        <a class="page-link" href="#" x-on:click.prevent="page > 1 && page--">&laquo;</a>
                    </li>
                    <template x-for="pageNumber in getPageNumbers()" x-bind:key="pageNumber">
                        <li class="page-item" x-bind:class="pageNumber === page ? 'active' : ''">
                            <a class="page-link" href="#" x-text="pageNumber" x-on:click.prevent="page = pageNumber"></a>
                        </li>
                    </template>
                    <li class="page-item" x-bind:class="page >= totalPages ? 'disabled' : ''">
                        <a class="page-link"  href="#" x-on:click.prevent="page < totalPages && page++">&raquo;</a>
                    </li>
                </ul>
            </nav>
        @endsection

        @yield('pagination')

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

        @yield('pagination')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha256-CDOy6cOibCWEdsRiZuaHf8dSGGJRYuBGC+mjoJimHGw=" crossorigin="anonymous"></script>
    <script>
        window.RouteList = () => ({
            groups: [],
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

            options: Alpine.$persist({
                expandMiddlewareGroup: false,
            }).as('rlw_options'),

            page: Alpine.$persist(1).as('rlw_page'),
            perPage: 50,
            totalPages: 0,

            init() {
                const enableDarkMode = (value) => (document.documentElement.dataset.bsTheme = value ? 'dark' : 'light');
                enableDarkMode(this.darkMode);

                this.$watch('darkMode', enableDarkMode);
                this.$watch('search', (value) => this.page = 1);

                this.$nextTick(() => {
                    this.groups = @json($groups);
                    this.routes = @json($routes);
                    this.loaded = true;
                });
            },

            refresh() {
                this.refreshing = true;

                fetch(@json(route('route:list')), {
                    headers: {
                        Accept: 'application/json',
                    },
                })
                    .then((response) => response.json())
                    .then((data) => {
                        this.routes = data.routes;
                        this.total = data.routes.length;
                        this.page = 1;
                        this.refreshing = false;
                    });
            },

            getRoutes() {
                let routes = Array.from(this.routes);
                let filtered = [];

                const stringSerializer = (value) => {
                    if (value === null || typeof value === 'undefined') {
                        return '';
                    } else if (Array.isArray(value)) {
                        return value.map(stringSerializer).join(' ');
                    } else if (typeof value === 'object') {
                        return Object.entries(value).map(([key, value]) => `${key} ${stringSerializer(value)}`).join(' ');
                    }

                    return String(value);
                };

                for (const _route of routes) {
                    const route = Object.assign({}, _route);
                    route.middleware = Array.from(_route.middleware);

                    if (this.columns.middleware && this.options.expandMiddlewareGroup) {
                        for (const [group, groupMiddlewares] of Object.entries(this.groups)) {
                            const index = route.middleware.indexOf(group);

                            if (index !== -1) {
                                route.middleware.splice(index, 1, ...groupMiddlewares);
                            }
                        }
                    }

                    if (this.search.value.trim().length > 0) {
                        let value;

                        if (this.search.column === 'all') {
                            value = stringSerializer(Object.values(route));
                        } else if (this.search.column === 'method' || this.search.column === 'middleware') {
                            value = stringSerializer(route[this.search.column]);
                        } else {
                            value = route[this.search.column];
                        }

                        if (value && value.length > 0 && value.toLowerCase().includes(this.search.value.trim().toLowerCase())) {
                            filtered.push(route);
                        }
                    } else {
                        filtered.push(route);
                    }
                }

                this.filteredCount = filtered.length;
                this.totalPages = Math.ceil(filtered.length / this.perPage);

                return filtered.slice((this.page - 1) * this.perPage, this.page * this.perPage);
            },

            getPageNumbers() {
                return [...Array(this.totalPages).keys()].map((number) => number + 1);
            },

            getMethodColor(method) {
                return (
                    'bg-' +
                    {
                        get: 'teal',
                        head: 'secondary',
                        options: 'info',
                        post: 'primary',
                        put: 'orange',
                        patch: 'orange',
                        delete: 'danger',
                        any: 'dark',
                    }[method.toLowerCase()]
                );
            },

            stylizeUri(domain, uri) {
                uri = domain ? domain + '/' + uri : uri;
                uri = uri.replace(/\/$/, '');
                uri = uri.replaceAll('{', '<span class="text-orange">{').replaceAll('}', '}</span>');

                return uri;
            },

            stylizeAction(action) {
                if (action.includes('@')) {
                    action = action.replace('@', '<span class="text-primary">@') + '</span>';
                }

                return action;
            },
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.14.1/dist/cdn.min.js" integrity="sha256-jFBwr6faTqqhp3sVi4/VTxJ0FpaF9YGZN1ZGLl/5QYM=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" integrity="sha256-NY2a+7GrW++i9IBhowd25bzXcH9BCmBrqYX5i8OxwDQ=" crossorigin="anonymous"></script>
</body>
</html>
