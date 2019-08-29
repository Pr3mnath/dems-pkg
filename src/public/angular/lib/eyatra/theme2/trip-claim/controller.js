app.component('eyatraTripClaimList', {
    templateUrl: eyatra_trip_claim_list_template_url,
    controller: function(HelperService, $rootScope, $scope, $http) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            trip_claim_filter_data_url
        ).then(function(response) {
            console.log(response.data);
            self.employee_list = response.data.employee_list;
            self.purpose_list = response.data.purpose_list;
            self.trip_status_list = response.data.trip_status_list;
            $rootScope.loading = false;
        });

        var dataTable = $('#eyatra_trip_claim_list_table').DataTable({
            stateSave: true,
            "dom": dom_structure_separate_2,
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
                url: laravel_routes['listEYatraTripClaimList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.employee_id = $('#employee_id').val();
                    d.purpose_id = $('#purpose_id').val();
                    d.status_id = $('#status_id').val();
                }
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'number', name: 'trips.number', searchable: true },
                { data: 'ecode', name: 'e.code', searchable: true },
                { data: 'start_date', name: 'v.date', searchable: true },
                { data: 'end_date', name: 'v.date', searchable: true },
                { data: 'cities', name: 'c.name', searchable: true },
                { data: 'purpose', name: 'purpose.name', searchable: true },
                { data: 'advance_received', name: 'trips.advance_received', searchable: false },
                { data: 'status', name: 'status.name', searchable: true },
            ],
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();

        setTimeout(function() {
            var x = $('.separate-page-header-inner.search .custom-filter').position();
            var d = document.getElementById('eyatra_trip_claim_list_table_filter');
            x.left = x.left + 15;
            d.style.left = x.left + 'px';
        }, 500);

        $scope.getEmployeeData = function(query) {
            $('#employee_id').val(query);
            dataTable.draw();
        }
        $scope.getPurposeData = function(query) {
            $('#purpose_id').val(query);
            dataTable.draw();
        }
        $scope.getStatusData = function(query) {
            $('#status_id').val(query);
            dataTable.draw();
        }

        $scope.reset_filter = function(query) {
            $('#employee_id').val(-1);
            $('#purpose_id').val(-1);
            $('#status_id').val(-1);
            dataTable.draw();
        }

        $scope.deleteTrip = function(id) {
            $('#del').val(id);
        }
        $scope.confirmDeleteTrip = function() {
            $id = $('#del').val();
            $http.get(
                eyatra_trip_claim_delete_url + '/' + $id,
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
                        text: 'Trips Deleted Successfully',
                    }).show();
                    $('#delete_emp').modal('hide');
                    dataTable.ajax.reload(function(json) {});
                }
            });
        }
        $rootScope.loading = false;

    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('eyatraTripClaimForm', {
    templateUrl: eyatra_trip_claim_form_template_url,
    controller: function($http, $location, HelperService, $routeParams, $rootScope, $element, $mdSelect, $scope, $timeout) {
        $form_data_url = typeof($routeParams.trip_id) == 'undefined' ? eyatra_trip_claim_form_data_url + '/' : eyatra_trip_claim_form_data_url + '/' + $routeParams.trip_id;
        var self = this;
        var lodgings_removal_id = [];
        var boardings_removal_id = [];
        var local_travels_removal_id = [];

        $scope.searchTravelMode;
        $scope.clearSearchTravelMode = function() {
            $scope.searchTravelMode = '';
        };

        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        self.eyatra_trip_claim_visit_attachment_url = eyatra_trip_claim_visit_attachment_url;
        $http.get(
            $form_data_url
        ).then(function(response) {
            /*if (!response.data.success) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: response.data.error,
                }).show();
                $location.path('/eyatra/trip/claim/list')
                $scope.$apply()
                return;
            }*/
            // console.log(response.data.trip);
            // console.log(response.data.travel_dates);
            //console.log(response.data.extras);
            self.employee = response.data.employee;
            self.travel_cities = response.data.travel_cities;
            self.travel_dates = response.data.travel_dates;
            self.extras = response.data.extras;
            self.trip = response.data.trip;
            self.action = response.data.action;
            self.travelled_cities_with_dates = response.data.travelled_cities_with_dates;
            self.lodge_cities = response.data.lodge_cities;
            if (self.action == 'Add') {
                self.trip.boardings = [];
                self.trip.lodgings = [];
                self.trip.local_travels = [];
                self.is_deviation = 0;

                $(self.lodge_cities).each(function(key, val) {
                    self.trip.lodgings.push({
                        id: '',
                        city_id: val.city_id,
                        city: {
                            name: val.city,
                        },
                        lodge_name: '',
                        stay_type_id: '',
                        eligible_amount: val.loadge_eligible_amount,
                        amount: '',
                        tax: '',
                        remarks: '',
                    });
                });

                $(self.travelled_cities_with_dates).each(function(key, val) {
                    $(val).each(function(k, v) {
                        self.trip.boardings.push({
                            id: '',
                            city_id: v.city_id,
                            city: {
                                name: v.city,
                            },
                            expense_name: '',
                            date: v.date,
                            amount: '',
                            remarks: '',
                            eligible_amount: v.board_eligible_amount,
                        });
                        self.trip.local_travels.push({
                            id: '',
                            city_id: v.city_id,
                            city: {
                                name: v.city,
                            },
                            mode_id: '',
                            date: v.date,
                            from: '',
                            to: '',
                            amount: '',
                            tax: '',
                            description: '',
                            eligible_amount: v.local_travel_eligible_amount,
                        });
                    });
                });
            } else {
                $timeout(function() { $scope.stayDaysEach(); }, 1000);
                self.is_deviation = self.trip.employee.trip_employee_claim.is_deviation;
            }
            self.lodgings_removal_id = [];
            self.boardings_removal_id = [];
            self.local_travels_removal_id = [];

            // if (self.trip.lodgings.length == 0) {
            //     self.addNewLodgings();
            // }
            // if (self.trip.boardings.length == 0) {
            //     self.addNewBoardings();
            // }
            // if (self.trip.local_travels.length == 0) {
            //     self.addNewLocalTralvels();
            // }
            setTimeout(function() {
                self.travelCal();
                self.lodgingCal();
                self.boardingCal();
                self.localTravelCal();
            }, 500);

            $rootScope.loading = false;

        });


        $scope.searchBookedBy;
        $scope.clearSearchBookedBy = function() {
            $scope.searchBookedBy = '';
        };

        $scope.searchLodgingCity;
        $scope.clearSearchLodgingCity = function() {
            $scope.searchLodgingCity = '';
        };

        $scope.searchStayType;
        $scope.clearSearchStayType = function() {
            $scope.searchStayType = '';
        };

        $scope.searchBoardingCity;
        $scope.clearSearchBoardingCity = function() {
            $scope.searchBoardingCity = '';
        };

        $scope.searchLocalTravelMode;
        $scope.clearSearchLocalTravelMode = function() {
            $scope.searchLocalTravelMode = '';
        };

        $scope.searchLocalTravelFrom;
        $scope.clearSearchLocalTravelFrom = function() {
            $scope.searchLocalTravelFrom = '';
        };

        $scope.searchLocalTravelTo;
        $scope.clearSearchLocalTravelTo = function() {
            $scope.searchLocalTravelTo = '';
        };

        $(document).on('mouseover', ".separate-btn-default", function() {
            var $this = $(this);
            console.log($this.data('text'));
            $this.tooltip({
                title: $this.data('text'),
                placement: "top"
            });
            $this.tooltip('show');
        });

        $(document).on('input', ".tooltip_remarks", function() {
            var value = $(this).val();
            console.log(' == value ==' + value);
            $(this).closest('.separate-btn-default').data('text', value);
        });

        $scope.getEligibleAmtBasedonCitycategoryGrade = function(grade_id, city_id, expense_type_id, key) {
            if (city_id && grade_id && expense_type_id) {
                // console.log(grade_id, city_id, expense_type_id, key);
                $.ajax({
                        url: get_eligible_amount_by_city_category_grade,
                        method: "GET",
                        data: { city_id: city_id, grade_id: grade_id, expense_type_id: expense_type_id },
                    })
                    .done(function(res) {
                        var eligible_amount = res.grade_expense_type ? res.grade_expense_type.eligible_amount : '0.00';
                        // console.log(' == eligible_amount ==' + eligible_amount);
                        if (expense_type_id == 3000) { //TRANSPORT EXPENSES
                            self.trip.self_visits[key].eligible_amount = eligible_amount;
                        } else if (expense_type_id == 3001) { // LODGING EXPENSE
                            self.trip.lodgings[key].eligible_amount = eligible_amount;
                        } else if (expense_type_id == 3002) { // BOARDING EXPENSE
                            self.trip.boardings[key].eligible_amount = eligible_amount;
                        } else if (expense_type_id == 3003) { // LOCAL TRAVEL EXPENSE
                            self.trip.local_travels[key].eligible_amount = eligible_amount;
                        }
                        $scope.$apply()
                    })
                    .fail(function(xhr) {
                        console.log(xhr);
                    });
            }
        }

        $scope.getEligibleAmtBasedonCitycategoryGradeStayType = function(grade_id, city_id, expense_type_id, key, stay_type_id) {
            if (city_id && grade_id && expense_type_id && stay_type_id) {
                $.ajax({
                        url: get_eligible_amount_by_city_category_grade_staytype,
                        method: "GET",
                        data: { city_id: city_id, grade_id: grade_id, expense_type_id: expense_type_id, stay_type_id: stay_type_id },
                    })
                    .done(function(res) {
                        var eligible_amount_after_per = res.eligible_amount;
                        self.trip.lodgings[key].eligible_amount = eligible_amount_after_per;
                        $scope.$apply()
                        $scope.stayDaysEach();
                    })
                    .fail(function(xhr) {
                        console.log(xhr);
                    });
            }
        }

        $scope.isDeviation = function() {
            var is_deviation = false;
            $('.is_deviation_amount').each(function() {
                var amount_entered = $(this).val();
                var amount_entered_eligible_amount = $(this).closest('.is_deviation_amount_row').find('.eligible_amount').val();
                if (!$.isNumeric(amount_entered)) {
                    amount_entered = 0;
                } else {
                    amount_entered = parseInt(amount_entered);
                }
                if (!$.isNumeric(amount_entered_eligible_amount)) {
                    amount_entered_eligible_amount = 0;
                } else {
                    amount_entered_eligible_amount = parseInt(amount_entered_eligible_amount);
                }
                if (amount_entered > amount_entered_eligible_amount) {
                    is_deviation = true;
                }
            });
            if (is_deviation) {
                $('#is_deviation').val(1);
            } else {
                $('#is_deviation').val(0);
            }
        }

        $scope.stayDaysEach = function() {
            $('.stayed_days').each(function() {
                var stayed_days = $(this).val();
                var stayed_base_eligible_amount = $(this).closest('tr').find('.base_eligible_amount').val();
                var stayed_eligible_amount_with_days = 0;

                if (!$.isNumeric(stayed_days)) {
                    stayed_eligible_amount_with_days = stayed_base_eligible_amount;
                } else {
                    stayed_eligible_amount_with_days = stayed_days * stayed_base_eligible_amount;
                }
                stayed_eligible_amount_with_days = parseFloat(Math.round(stayed_eligible_amount_with_days * 100) / 100).toFixed(2);

                $(this).closest('tr').find('.eligible_amount').val(stayed_eligible_amount_with_days);
                $(this).closest('tr').find('.eligible_amount_label').html('Eligible - ₹ ' + stayed_eligible_amount_with_days);
                $scope.isDeviation();
            });
        }

        $(document).on('input', '.stayed_days', function() {
            var stayed_days = $(this).val();
            var stayed_base_eligible_amount = $(this).closest('tr').find('.base_eligible_amount').val();
            var stayed_eligible_amount_with_days = 0;

            if (!$.isNumeric(stayed_days)) {
                stayed_eligible_amount_with_days = stayed_base_eligible_amount;
            } else {
                stayed_eligible_amount_with_days = stayed_days * stayed_base_eligible_amount;
            }
            stayed_eligible_amount_with_days = parseFloat(Math.round(stayed_eligible_amount_with_days * 100) / 100).toFixed(2);

            $(this).closest('tr').find('.eligible_amount').val(stayed_eligible_amount_with_days);
            $(this).closest('tr').find('.eligible_amount_label').html('Eligible - ₹ ' + stayed_eligible_amount_with_days);
            $scope.isDeviation();
        });

        /*self.addLodgingExpenses = function() {
            self.lodging.push({
                visit_date: '',
                booking_method: 'Self',
                preferred_travel_modes: '',
            });
        }*/
        // $(function() {
        //     $('.form_datetime').datetimepicker();
        // });

        // $(".form_datetime").datetimepicker({
        //     format: "yyyy-m-dd hh:ii",
        //     autoclose: true,
        //     todayBtn: true,
        //     pickerPosition: "bottom-left"
        // });

        /* Pane Next Button */
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
        });

        //Get lodge days between checkin & checkout
        $(document).on('change', '.lodging_checkin_out_date', function() {
            var checkin_date = $(this).closest('tr').find('.lodging_checkin_date').val();
            var checkout_date = $(this).closest('tr').find('.lodging_check_out_date').val();
            var base_eligible_amount = $(this).closest('tr').find('.base_eligible_amount').val();

            var date_1 = checkin_date.split("-");
            var date_2 = checkout_date.split("-");
            var checkin_date_format = date_1[1] + '/' + date_1[0] + '/' + date_1[2];
            var checkout_date_format = date_2[1] + '/' + date_2[0] + '/' + date_2[2];
            if (checkin_date_format && checkout_date_format) {
                var timeDiff = (new Date(checkout_date_format)) - (new Date(checkin_date_format));
                var days = (timeDiff / (1000 * 60 * 60 * 24)) + 1;
                var eligible_amount_with_days = 0;
                var days_c = '';
                if (!$.isNumeric(days)) {
                    days_c = '';
                    eligible_amount_with_days = base_eligible_amount;
                } else {
                    days_c = days;
                    eligible_amount_with_days = days * base_eligible_amount;
                }
                eligible_amount_with_days = parseFloat(Math.round(eligible_amount_with_days * 100) / 100).toFixed(2);
                $(this).closest('tr').find('.stayed_days').val(days_c);
                $(this).closest('tr').find('.eligible_amount').val(eligible_amount_with_days);
                $(this).closest('tr').find('.eligible_amount_label').html('Eligible - ₹ ' + eligible_amount_with_days);
                $scope.isDeviation();
            }
        });
        //Check Departure Date & Arrival Date Value Exist
        $scope.CheckDateValExist = function() {
            var form_id = '#claim_form';
            var transport_expense_date_count = $('.transport_expense_date').length;
            var transport_expense_date_value_exist_count = 0;
            $('.transport_expense_date').each(function() {
                var date_exist = $(this).val();
                if (date_exist) {
                    transport_expense_date_value_exist_count++;
                }
            });
            if (transport_expense_date_count == transport_expense_date_value_exist_count) {
                let formData = new FormData($(form_id)[0]);
                $.ajax({
                        url: eyatra_trip_get_expense_data_url,
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        console.log(res);
                        self.trip.boardings = [];
                        self.trip.lodgings = [];
                        self.trip.local_travels = [];

                        $(res['lodge_cities']).each(function(key, val) {
                            self.trip.lodgings.push({
                                id: '',
                                city_id: val.city_id,
                                city: {
                                    name: val.city,
                                },
                                lodge_name: '',
                                stay_type_id: '',
                                eligible_amount: val.loadge_eligible_amount,
                                amount: '',
                                tax: '',
                                remarks: '',
                            });
                        });

                        $(res['travelled_cities_with_dates']).each(function(key, val) {
                            $(val).each(function(k, v) {
                                self.trip.boardings.push({
                                    id: '',
                                    city_id: v.city_id,
                                    city: {
                                        name: v.city,
                                    },
                                    expense_name: '',
                                    date: v.date,
                                    amount: '',
                                    remarks: '',
                                    eligible_amount: v.board_eligible_amount,
                                });
                                self.trip.local_travels.push({
                                    id: '',
                                    city_id: v.city_id,
                                    city: {
                                        name: v.city,
                                    },
                                    mode_id: '',
                                    date: v.date,
                                    from: '',
                                    to: '',
                                    amount: '',
                                    tax: '',
                                    description: '',
                                    eligible_amount: v.local_travel_eligible_amount,
                                });
                            });
                        });
                        $scope.$apply()
                        // console.log(self.trip.boardings);
                    })
                    .fail(function(xhr) {
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        }

        // Lodgings
        self.addNewLodgings = function() {
            self.trip.lodgings.push({
                id: '',
                city_id: '',
                lodge_name: '',
                stay_type_id: '',
                eligible_amount: '0.00',
                amount: '',
                tax: '',
                remarks: '',
            });
        }
        self.removeLodging = function(index, lodging_id) {
            if (lodging_id) {
                self.lodgings_removal_id.push(lodging_id);
                $('#lodgings_removal_id').val(JSON.stringify(self.lodgings_removal_id));
            }
            self.trip.lodgings.splice(index, 1);
            setTimeout(function() {
                self.lodgingCal();
            }, 500);
        }

        // Boardings
        self.addNewBoardings = function() {
            self.trip.boardings.push({
                id: '',
                city_id: '',
                expense_name: '',
                date: '',
                amount: '',
                remarks: '',
                eligible_amount: '0.00',
            });
        }
        self.removeBoarding = function(index, boarding_id) {
            if (boarding_id) {
                self.boardings_removal_id.push(boarding_id);
                $('#boardings_removal_id').val(JSON.stringify(self.boardings_removal_id));
            }
            self.trip.boardings.splice(index, 1);
            setTimeout(function() {
                self.boardingCal();
            }, 500);
        }

        // LocalTralvels
        self.addNewLocalTralvels = function() {
            self.trip.local_travels.push({
                id: '',
                mode_id: '',
                date: '',
                from_id: '',
                to_id: '',
                amount: '',
                description: '',
                eligible_amount: '0.00',
            });
        }
        self.removeLocalTralvel = function(index, local_travel_id) {
            if (local_travel_id) {
                self.local_travels_removal_id.push(local_travel_id);
                $('#local_travels_removal_id').val(JSON.stringify(self.local_travels_removal_id));
            }
            self.trip.local_travels.splice(index, 1);
            setTimeout(function() {
                self.localTravelCal();
            }, 500);
        }
        self.travelCal = function() {
            var total_travel_amount = 0;
            $('.travel_amount').each(function() {
                var travel_amount = parseInt($(this).closest('.is_deviation_amount_row').find('#travel_amount').val() || 0);
                //alert(lodging_amount);
                var travel_tax = parseInt($(this).closest('.is_deviation_amount_row').find('#travel_tax').val() || 0);
                if (!$.isNumeric(travel_amount)) {
                    travel_amount = 0;
                }
                if (!$.isNumeric(travel_tax)) {
                    travel_tax = 0;
                }
                travel_current_total = travel_amount + travel_tax;
                total_travel_amount += travel_current_total;
                $(this).closest('tr').find('.visit_booking_total_amount').val(travel_current_total);
            });
            // console.log(total_travel_amount);
            $('.transport_expenses').text('₹ ' + total_travel_amount.toFixed(2));
            $('.total_travel_amount').val(total_travel_amount.toFixed(2));
            caimTotalAmount();
        }
        self.lodgingCal = function() {
            var total_lodging_amount = 0;
            $('.lodging_amount').each(function() {
                var lodging_amount = parseInt($(this).closest('.is_deviation_amount_row').find('#lodging_amount').val() || 0);
                //alert(lodging_amount);
                var lodging_tax = parseInt($(this).closest('.is_deviation_amount_row').find('#lodging_tax').val() || 0);
                if (!$.isNumeric(lodging_amount)) {
                    lodging_amount = 0;
                }
                if (!$.isNumeric(lodging_tax)) {
                    lodging_tax = 0;
                }
                current_total = lodging_amount + lodging_tax;
                total_lodging_amount += current_total;
                $(this).closest('tr').find('.lodge_total_amount').val(current_total);
            });
            // console.log(total_lodging_amount);
            $('.lodging_expenses').text('₹ ' + total_lodging_amount.toFixed(2));
            $('.total_lodging_amount').val(total_lodging_amount.toFixed(2));
            caimTotalAmount();
        }

        self.boardingCal = function() {
            //alert();
            var total_boarding_amount = 0;
            $('.boarding_amount').each(function() {
                var boarding_amount = parseFloat($(this).closest('.is_deviation_amount_row').find('#boarding_amount').val() || 0);
                var boarding_tax = parseFloat($(this).closest('.is_deviation_amount_row').find('#boarding_tax').val() || 0);
                //console.log(boarding_amount, boarding_tax);
                if (!$.isNumeric(boarding_amount)) {
                    boarding_amount = 0;
                }
                if (!$.isNumeric(boarding_tax)) {
                    boarding_tax = 0;
                }
                current_boarding_total = boarding_amount + boarding_tax;
                total_boarding_amount += current_boarding_total;
            });
            // console.log(total_boarding_amount);
            $('.boarding_expenses').text('₹ ' + total_boarding_amount.toFixed(2));
            $('.total_boarding_amount').val(total_boarding_amount.toFixed(2));
            caimTotalAmount();
        }

        self.localTravelCal = function() {
            //alert();
            var total_local_travel_amount = 0;
            $('.local_travel_amount').each(function() {
                var local_travel_amount = parseFloat($(this).closest('.is_deviation_amount_row').find('#local_travel_amount').val() || 0);
                var local_travel_tax = parseFloat($(this).closest('.is_deviation_amount_row').find('#local_travel_tax').val() || 0);
                // console.log(local_travel_amount, local_travel_tax);
                if (!$.isNumeric(local_travel_amount)) {
                    local_travel_amount = 0;
                }
                if (!$.isNumeric(local_travel_tax)) {
                    local_travel_tax = 0;
                }
                current_boarding_total = local_travel_amount + local_travel_tax;
                total_local_travel_amount += current_boarding_total;
            });
            // console.log(total_local_travel_amount);
            $('.local_expenses').text('₹ ' + total_local_travel_amount.toFixed(2));
            $('.total_local_travel_amount').val(total_local_travel_amount.toFixed(2));
            caimTotalAmount();
        }

        function caimTotalAmount() {

            var total_travel_amount = parseFloat($('.total_travel_amount').val() || 0);
            var total_lodging_amount = parseFloat($('.total_lodging_amount').val() || 0);
            var total_boarding_amount = parseFloat($('.total_boarding_amount').val() || 0);
            var total_local_travel_amount = parseFloat($('.total_local_travel_amount').val() || 0);
            var total_claim_amount = total_travel_amount + total_lodging_amount + total_boarding_amount + total_local_travel_amount;
            $('.claim_total_amount').val(total_claim_amount.toFixed(2));
            $('.claim_total_amount').text('₹ ' + total_claim_amount.toFixed(2));

            // console.log('total claim' + total_claim_amount);
        }

        //Form submit validation
        self.claimSubmit = function() {
            //alert();
            // $('#claim_form').on('submit', function(event) {
            //Add validation rule for dynamically generated name fields
            $('.maxlength_name').each(function() {
                $(this).rules("add", {
                    required: true,
                    maxlength: 191,
                });
            });
            $('.num_amount').each(function() {
                $(this).rules("add", {
                    maxlength: 12,
                    number: true,
                    // required: true,
                });
            });
            $('.boarding_expense').each(function() {
                $(this).rules("add", {
                    maxlength: 255,
                    required: true,
                });
            });
        }

        var form_id = '#claim_form';
        $.validator.addClassRules({
            // maxlength_name: {
            //     maxlength: 191,
            //     required: true,
            // },
            // num_amount: {
            //     maxlength: 12,
            //     number: true,
            //     required: true,
            // },
            // boarding_expense: {
            //     maxlength: 255,
            //     required: true,
            // }
            attachments: {
                // extension: "xlsx,xls",
            }
        });

        var v = jQuery(form_id).validate({
            invalidHandler: function(event, validator) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'Kindly check in each tab to fix errors'
                }).show();
            },
            ignore: "",
            rules: {},
            submitHandler: function(form) {
                //console.log(self.item);
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: eyatra_trip_claim_save_url,
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        //console.log(res.success);
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
                                text: 'Claim saved successfully!!',
                            }).show();

                            $location.path('/eyatra/trip/claim/list')
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
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('eyatraTripClaimView', {
    templateUrl: eyatra_trip_claim_view_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope) {
        $form_data_url = typeof($routeParams.trip_id) == 'undefined' ? eyatra_trip_claim_view_url + '/' : eyatra_trip_claim_view_url + '/' + $routeParams.trip_id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        self.eyatra_trip_claim_visit_attachment_url = eyatra_trip_claim_visit_attachment_url;
        self.eyatra_trip_claim_lodging_attachment_url = eyatra_trip_claim_lodging_attachment_url;
        self.eyatra_trip_claim_boarding_attachment_url = eyatra_trip_claim_boarding_attachment_url;
        self.eyatra_trip_claim_local_travel_attachment_url = eyatra_trip_claim_local_travel_attachment_url;
        $http.get(
            $form_data_url
        ).then(function(response) {
            if (!response.data.success) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: response.data.error,
                }).show();
                $location.path('/eyatra/trip/claim/list')
                $scope.$apply()
                return;
            }
            console.log(response.data.trip.lodgings.city);
            self.trip = response.data.trip;
            self.travel_cities = response.data.travel_cities;
            self.travel_dates = response.data.travel_dates;
            self.transport_total_amount = response.data.transport_total_amount;
            self.lodging_total_amount = response.data.lodging_total_amount;
            self.boardings_total_amount = response.data.boardings_total_amount;
            self.local_travels_total_amount = response.data.local_travels_total_amount;
            self.total_amount = response.data.total_amount;
            if (self.trip.advance_received) {
                if (self.total_amount > self.trip.advance_received) {
                    self.pay_to_employee = (self.total_amount - self.trip.advance_received);
                    self.pay_to_company = 0.00;
                } else if (self.total_amount < self.trip.advance_received) {
                    self.pay_to_employee = 0.00;
                    self.pay_to_company = (self.total_amount - self.trip.advance_received);
                } else {
                    self.pay_to_employee = 0.00;
                    self.pay_to_company = 0.00;
                }
            } else {
                self.pay_to_employee = self.total_amount;
                self.pay_to_company = 0.00;
            }
            $rootScope.loading = false;

        });

        /* Pane Next Button */
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
        });

    }
});