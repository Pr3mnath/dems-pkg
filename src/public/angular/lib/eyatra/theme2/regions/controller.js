app.component('eyatraRegions', {
    templateUrl: eyatra_region_list_template_url,
    controller: function(HelperService, $rootScope, $http, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#eyatra_region_table').DataTable({
            stateSave: true,
            "dom": dom_structure_separate,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows Per Page _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            pageLength: 10,
            processing: true,
            serverSide: true,
            paging: true,
            ordering: false,
            ajax: {
                url: laravel_routes['listEYatraRegion'],
                type: "GET",
                dataType: "json",
                data: function(d) {}
            },

            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'name', name: 'regions.name', searchable: true },
                { data: 'code', name: 'regions.code', searchable: true },
                { data: 'state_name', name: 'nstates.name', searchable: true },
                { data: 'status', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        $('.separate-page-header-content .data-table-title').html('<p class="breadcrumb">Masters / Regions</p><h3 class="title">Regions</h3>');
        $('.add_new_button').html(
            '<a href="#!/eyatra/region/add" type="button" class="btn btn-secondary" ng-show="$ctrl.hasPermission(\'add-region\')">' +
            'Add New' +
            '</a>'
        );

        $scope.deleteRegion = function(id) {
            $('#del').val(id);
        }
        $scope.confirmDeleteRegion = function() {
            $id = $('#del').val();
            $http.get(
                region_delete_url + '/' + $id,
            ).then(function(response) {
                if (!response.data.success) {
                    var errors = '';
                    for (var i in res.errors) {
                        errors += '<li>' + res.errors[i] + '</li>';
                    }
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: errors
                    }).show();
                } else {
                    new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Region Deleted Successfully',
                    }).show();
                    $('#delete_region').modal('hide');
                    dataTable.ajax.reload(function(json) {});
                }

            });
        }

        $rootScope.loading = false;

    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('eyatraRegionForm', {
    templateUrl: region_form_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope) {
        $form_data_url = typeof($routeParams.region_id) == 'undefined' ? region_form_data_url : region_form_data_url + '/' + $routeParams.region_id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;

        $http.get(
            $form_data_url
        ).then(function(response) {
            if (!response.data.success) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: response.data.error,
                }).show();
                $location.path('/eyatra/regions')
                $scope.$apply()
                return;
            }
            self.region = response.data.region;
            self.extras = response.data.extras;
            self.action = response.data.action;
            self.status = response.data.status;

            if (self.action == 'Edit') {
                $scope.getStateByCountry(self.region.state.country_id);
            }
            $rootScope.loading = false;

        });

        $scope.getStateByCountry = function(country_id) {
            if (country_id) {
                $.ajax({
                        url: region_get_state_by_country,
                        method: "POST",
                        data: { country_id: country_id },
                    })
                    .done(function(res) {
                        self.extras.state_list = [];
                        self.extras.state_list = res.state_list;
                        $scope.$apply()
                    })
                    .fail(function(xhr) {
                        console.log(xhr);
                    });
            }
        }



        var form_id = '#region_form';
        var v = jQuery(form_id).validate({
            errorPlacement: function(error, element) {
                error.insertAfter(element)
            },
            ignore: '',
            rules: {
                'code': {
                    required: true,
                    maxlength: 191,
                    minlength: 3,
                },
                'name': {
                    required: true,
                    maxlength: 80,
                    minlength: 3,
                },
                'outlet_id': {
                    required: true,
                },
                'reporting_to_id': {
                    required: true,
                },
            },
            messages: {
                'code': {
                    maxlength: 'Please enter maximum of 191 letters',
                },
                'name': {
                    maxlength: 'Please enter maximum of 80 letters',
                },
            },
            submitHandler: function(form) {

                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEYatraRegion'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        console.log(res.success);
                        if (!res.success) {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
                        } else {
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Region updated successfully',
                            }).show();
                            $location.path('/eyatra/regions')
                            $scope.$apply()
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            },
        });

    }
});

app.component('eyatraRegionView', {
    templateUrl: region_view_template_url,
    controller: function($http, $location, $routeParams, HelperService, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            region_view_url + '/' + $routeParams.region_id
        ).then(function(response) {
            self.region = response.data.region;
            $rootScope.loading = false;
        });
    }
});


//------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------