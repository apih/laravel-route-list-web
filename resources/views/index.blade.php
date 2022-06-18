<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Route List Web</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-YvdLHPgkqJ8DVUxjjnGVlMMJtNimJ6dYkowFFvp4kKs=" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;700&display=swap" rel="stylesheet">

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

        .nowrap {
            white-space: nowrap;
        }

        .text-indigo {
            color: #6610f2;
        }

        .text-orange {
            color: #fd7e14;
        }

        .bg-green {
            background-color: #28a745;
        }

        .bg-orange {
            background-color: #fd7e14;
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
            <div class="col-md-10">
                <input type="text" class="form-control" placeholder="Search here..." x-model="search.value" x-on:keydown.escape="search.value = ''">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-10">
                @foreach ($columns as $column => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="columns.{{ $column }}" x-model="columns.{{ $column }}">
                        <label class="form-check-label" for="columns.{{ $column }}">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="col-md-2">
                <div class="fw-bold float-end">
                    <span x-show="filteredCount !== routes.length" x-cloak><span x-text="filteredCount"></span> of</span> {{ $routes->count() }} routes
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm">
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
                    <template x-for="route in getRoutes()">
                        <tr>
                            <td x-show="columns.method" style="width: 120px">
                                <template x-for="method in route.method">
                                    <span>
                                        <span class="badge" x-bind:class="getMethodColor(method)" x-text="method"></span>
                                    </span>
                                </template>
                            </td>
                            <td class="font-monospace" x-show="columns.uri || columns.name">
                                <div class="fw-medium nowrap" x-html="stylizeUri(route.domain, route.uri)" x-show="columns.uri"></div>
                                <div x-text="route.name" x-show="columns.name"></div>
                            </td>
                            <td class="font-monospace" x-show="columns.action || columns.middleware">
                                <div class="fw-medium" x-html="stylizeAction(route.action)" x-show="columns.action"></div>
                                <template x-if="columns.middleware">
                                    <div class="small">
                                        <template x-for="item in route.middleware">
                                            <div x-text="item"></div>
                                        </template>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha256-9SEPo+fwJFpMUet/KACSwO+Z/dKMReF9q4zFhU/fT9M=" crossorigin="anonymous"></script>
    <script>
        window.RouteList = function () {
            return {
                routes: @json($routes),
                filteredCount: @json($routes->count()),

                search: {
                    column: 'all',
                    value: '',
                },

                columns: {
                    @foreach ($columns as $column => $label)
                        {{ $column }}: true,
                    @endforeach
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
                        get: 'green',
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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.2/dist/cdn.min.js" integrity="sha256-0Vc6RcGUGe6IHT9+bWgQu5VeoNZEcNofGHVTfeGMYD4=" crossorigin="anonymous"></script>
</body>
</html>
