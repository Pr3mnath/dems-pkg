app.component('eyatraGrades', {
    templateUrl: eyatra_grade_list_template_url,
    controller: function(HelperService, $rootScope, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var dataTable = $('#eyatra_grade_table').DataTable({
            "dom": dom_structure,
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
                url: laravel_routes['listEYatraGrade'],
                type: "GET",
                dataType: "json",
                data: function(d) {}
            },

            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'grade_name', name: 'entities.name', searchable: false },
                { data: 'expense_count', searchable: false },
                { data: 'travel_count', searchable: true },
                { data: 'trip_count', searchable: true },
                { data: 'status', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        $('.page-header-content .display-inline-block .data-table-title').html('Grades List');
        $('.add_new_button').html(
            '<a href="#!/eyatra/grade/add" type="button" class="btn btn-secondary" ng-show="$ctrl.hasPermission(\'add-trip\')">' +
            'Add New' +
            '</a>'
        );
        $rootScope.loading = false;

        $scope.deleteDiscount = function($id) {
            $('#del').val($id);
        }
        $scope.confirmDeleteDiscount = function() {
            //return confirm(‘Are You sure ‘);
            $id = $('#del').val();
            $http.get(
                delete_discount_url + '/' + $id,
            ).then(function(response) {
                console.log(response.data);
                if (response.data.success) {

                    new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Discount Deleted Successfully',
                    }).show();
                } else {
                    new Noty({
                        type: 'error',
                        layout: 'topRight',
                        text: 'Discount not Deleted',
                    }).show();
                }
                $('#delete_discount').modal('hide');
                dataTable.ajax.reload(function(json) {});
            });
        }


    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('eyatraGradeForm', {
    templateUrl: grade_form_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope, $timeout) {
        $form_data_url = typeof($routeParams.grade_id) == 'undefined' ? grade_form_data_url : grade_form_data_url + '/' + $routeParams.grade_id;
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
                $location.path('/eyatra/grades')
                $scope.$apply()
                return;
            }
            self.entity = response.data.entity;
            self.extras = response.data.extras;
            self.action = response.data.action;
            $rootScope.loading = false;

            if (self.action == 'Edit') {
                if (self.entity.deleted_at == null) {
                    self.switch_value = 'Active';
                } else {
                    self.switch_value = 'Inactive';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
        });



        $('.toggle_cb').on('click', function() {
            var class_name = $(this).data('class');
            if (event.target.checked == true) {
                $('.' + class_name).prop('checked', true);
                if (class_name == 'expense_cb') {
                    $.each($('.' + class_name + ':checked'), function() {
                        $scope.getexpense_type($(this).val());
                    });
                }

            } else {
                $('.' + class_name).prop('checked', false);
                if (class_name == 'expense_cb') {
                    $.each($('.' + class_name), function() {
                        $scope.getexpense_type($(this).val());
                    });
                }
            }
        });

        $scope.getexpense_type = function(id) {
            if (event.target.checked == true) {
                $(".sub_class_" + id).removeClass("ng-hide");
                $(".sub_class_" + id).prop('required', true);
            } else {
                $(".sub_class_" + id).addClass("ng-hide");
                $(".sub_class_" + id).prop('required', false);
            }
        }

        $(document).on('click', '.expense_cb', function() {
            var id = $(this).val();
            if ($(this).prop("checked") == true) {
                $(".sub_class_" + id).prop('required', true);
            } else {
                $(".sub_class_" + id).prop('required', false);
            }

        });

        var form_id = '#grade-form';
        var v = jQuery(form_id).validate({
            errorPlacement: function(error, element) {
                error.insertAfter(element)
            },
            ignore: '',
            rules: {
                'grade_name': {
                    required: true,
                },
            },
            messages: {
                'grade_name': {
                    required: 'Please enter Grade Name',
                },
            },
            submitHandler: function(form) {

                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEYatraGrade'],
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
                                text: 'Grade saved successfully',
                            }).show();
                            $location.path('/eyatra/grades')
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

app.component('eyatraGradeView', {
    templateUrl: grade_view_template_url,

    controller: function($http, $location, $routeParams, HelperService, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            grade_view_url + '/' + $routeParams.grade_id
        ).then(function(response) {
            self.grade = response.data.grade;
            self.expense_type_list = response.data.expense_type_list;
            self.localtravel_list = response.data.localtravel_list;
            self.travel_purpose_list = response.data.travel_purpose_list;
            self.action = response.data.action;
            if (self.grade.deleted_at == null) {
                self.status = 'Active';
            } else {
                self.status = 'Inactive';
            }
        });
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
        });
    }
});


//------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------