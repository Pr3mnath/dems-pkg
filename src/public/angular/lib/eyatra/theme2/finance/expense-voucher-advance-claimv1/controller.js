app.component('eyatraExpenseVoucherAdvanceVerificationList', {
    templateUrl: expense_voucher_advance_verification_list_template_url,
    controller: function(HelperService, $rootScope, $scope, $http, $routeParams, $location) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;

        $list_data_url = expense_voucher_advance_verification_list_data_url;

        var dataTable = $('#expense_advance_verification_list').DataTable({
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
            retrieve: true,
            ajax: {
                url: $list_data_url,
                type: "GET",
                dataType: "json",
                data: function(d) {
                    //d.type_id = $routeParams.type_id;
                }
            },

            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'ename', name: 'users.name', searchable: true },
                { data: 'ecode', name: 'employees.code', searchable: true },
                { data: 'date', name: 'date', searchable: false },
                { data: 'advance_amount', name: 'expense_voucher_advance_requests.advance_amount', searchable: false },
                { data: 'balance_amount', name: 'expense_voucher_advance_requests.balance_amount', searchable: false },
                { data: 'status', name: 'configs.name', searchable: false },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        $('.separate-page-header-content .data-table-title').html('<p class="breadcrumb">Expense Voucher / Expense Voucher Advances list</p><h3 class="title">Expense Voucher Advances</h3>');

        // $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('eyatraExpenseVoucherAdvanceVerificationView', {
    templateUrl: expense_voucher_advance_verification_view_template_url,
    controller: function($http, $location, $routeParams, HelperService, $rootScope, $timeout) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            expense_voucher_advance_verification_view_data_url + '/' + $routeParams.id
        ).then(function(response) {
            console.log(response);
            self.expense_voucher_view = response.data.expense_voucher_view;
            self.rejection_list = response.data.rejection_list;
        });

        var form_id = '#approve';
        var v = jQuery(form_id).validate({
            ignore: '',
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['expenseVoucherVerificationSave'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (!res.success) {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
                        } else {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Petty Cash Approved successfully',
                                animation: {
                                    speed: 500 // unavailable - no need
                                },
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 5000);
                            $("#alert-modal-approve").modal('hide');
                            $timeout(function() {
                                $location.path('/eyatra/expense/voucher-advance/verification1/')
                            }, 500);
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            },
        });
        var form_id1 = '#reject';
        var v = jQuery(form_id1).validate({
            ignore: '',
            rules: {
                'remarks': {
                    required: true,
                    maxlength: 191,
                },
            },
            messages: {
                'remarks': {
                    required: 'Enter Remarks',
                    maxlength: 'Enter Maximum 191 Characters',
                },
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id1)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['expenseVoucherVerificationSave'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (!res.success) {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
                        } else {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Petty Cash Rejected successfully',
                                animation: {
                                    speed: 500 // unavailable - no need
                                },
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 5000);
                            $(".remarks").val('');
                            $("#alert-modal-reject").modal('hide');
                            $timeout(function() {
                                $location.path('/eyatra/expense/voucher-advance/verification1/')
                            }, 500);
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            },
        });

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------