<?php
//AUTH
Route::post('eyatra/api/login', 'Uitoux\EYatra\Api\AuthController@login');

//FORGET PASSWORD
Route::post('eyatra/api/forgotPassword', 'Uitoux\EYatra\Api\AuthController@forgotPassword');
Route::post('eyatra/api/changePassword', 'Uitoux\EYatra\Api\AuthController@changePassword');

Route::group(['middleware' => ['api']], function () {
	Route::group(['middleware' => ['auth:api'], 'prefix' => 'eyatra/api'], function () {
		//HELPERS
		Route::get('city/search', 'Uitoux\EYatra\Api\CityController@searchCity');
		Route::post('state/get', 'Uitoux\EYatra\Api\StateController@getStateList');
		Route::post('city/get', 'Uitoux\EYatra\Api\CityController@getCityList');

		//TRIPS
		Route::post('trip/list', 'Uitoux\EYatra\Api\TripController@listTrip');
		Route::post('trip/get-form-data', 'Uitoux\EYatra\Api\TripController@getTripFormData');
		Route::post('trip/add', 'Uitoux\EYatra\Api\TripController@addTrip');
		Route::post('trip/view/{trip_id}', 'Uitoux\EYatra\Api\TripController@viewTrip');
		Route::get('trip/delete/{trip_id}', 'Uitoux\EYatra\Api\TripController@deleteTrip');
		Route::get('trip/cancel/{trip_id}', 'Uitoux\EYatra\Api\TripController@cancelTrip');
		Route::get('trip/visit/request-booking-cancel/{visit_id}', 'Uitoux\EYatra\Api\TripController@requestCancelVisitBooking');
		Route::get('trip/visit/booking-cancel/{visit_id}', 'Uitoux\EYatra\Api\TripController@cancelTripVisitBooking');
		Route::get('trips/visit/delete/{visit_id}', 'Uitoux\EYatra\Api\TripController@deleteVisit');

		// TRIP CLAIM
		// Route::get('trip/cliam/list', 'Uitoux\EYatra\Api\TripVerificationController@listClaimList');
		Route::post('trip/claim/list', 'Uitoux\EYatra\Api\TripClaimController@listClaimList');
		Route::post('trip/claim/view/{trip_id}', 'Uitoux\EYatra\Api\TripClaimController@getClaimViewData');
		Route::post('trip/claim/get-form-data/{trip_id}', 'Uitoux\EYatra\Api\TripClaimController@getClaimFormData');
		Route::post('trip/claim/get-attachments', 'Uitoux\EYatra\Api\TripClaimController@getTripClaimAttachments');

		Route::post('trip/claim/get-eligible-amount', 'Uitoux\EYatra\Api\TripClaimController@getEligibleAmtBasedonCitycategoryGrade');
		Route::post('trip/claim/get-eligible-amount/by-staytype', 'Uitoux\EYatra\Api\TripClaimController@getEligibleAmtBasedonCitycategoryGradeStaytype');
		Route::post('trip/claim/get-visit-transport-mode-claim-status', 'Uitoux\EYatra\Api\TripClaimController@getVisitTrnasportModeClaimStatus');

		Route::post('trip/claim/save', 'Uitoux\EYatra\Api\TripClaimController@saveClaim');

		//TRIP VERIFICATION
		Route::get('trip/verification/get-list', 'Uitoux\EYatra\Api\TripVerificationController@listTripVerification');
		Route::post('trip/verification/approve/{trip_id}', 'Uitoux\EYatra\Api\TripVerificationController@approveTrip');
		Route::post('trip/verification/reject', 'Uitoux\EYatra\Api\TripVerificationController@rejectTrip');

		//TRIP CLAIM VERIFICATION LEVEL
		Route::get('trip/claim/verification/one/get-list', 'Uitoux\EYatra\Api\TripClaimVerificationLevelController@listTripClaimVerificationOneList');
		Route::get('trip/claim/verification/view/{trip_id?}', 'Uitoux\EYatra\Api\TripClaimVerificationLevelController@getClaimVerificationViewData');
		Route::post('trip/claim/verification/one/reject', 'Uitoux\EYatra\Api\TripClaimVerificationLevelController@rejectTripClaimVerificationOne');
		Route::get('trip/claim/verification/one/approve/{trip_id}', 'Uitoux\EYatra\Api\TripClaimVerificationLevelController@approveTripClaimVerificationOne');

		//TRIP REJECTION REASON
		Route::get('trip/rejection/reasons', 'Uitoux\EYatra\Api\TripVerificationController@getRejectionData');

		//DASHBOARD
		Route::get('eyatra/dashboard', 'Uitoux\EYatra\Api\DashboardController@getDashboard');

		//DIGITAL SIGNATURE
		Route::post('petty-cash/digital-signature-attachments', 'Uitoux\EYatra\Api\TripClaimController@getdigitalsignatureAttachments');

		//LOCAL TRIP
		Route::post('local-trip/list', 'Uitoux\EYatra\Api\LocalTripController@listLocalTrip');
		Route::post('local-trip/get-form-data', 'Uitoux\EYatra\Api\LocalTripController@getTripFormData');
		Route::post('local-trip/save', 'Uitoux\EYatra\Api\LocalTripController@saveLocalTrip');
		Route::get('local-trip/view/{trip_id}', 'Uitoux\EYatra\Api\LocalTripController@viewTrip');
		Route::get('local-trip/cancel/{trip_id}', 'Uitoux\EYatra\Api\LocalTripController@cancelTrip');
		Route::get('local-trip/delete/{trip_id}', 'Uitoux\EYatra\Api\LocalTripController@deleteTrip');
		Route::post('local-trip/save/attachments', 'Uitoux\EYatra\Api\LocalTripController@saveAttachments');

		//LOCAL TRIP VERIFICATION
		Route::post('local-trip/verification/get-list', 'Uitoux\EYatra\Api\LocalTripController@listTripVerification');

		Route::get('local-trip/verification/approve/{trip_id}', 'Uitoux\EYatra\Api\LocalTripController@approveTrip');
		Route::post('local-trip/verification/reject', 'Uitoux\EYatra\Api\LocalTripController@rejectTrip');

		//PROFILE IMAGE SAVE
		Route::post('/profile/save/image', 'Uitoux\EYatra\Api\ProfileController@saveImage')->name('profileSaveImage');

		//NOTIFICATION
		Route::post('notification/get-list', 'Uitoux\EYatra\Api\DashboardController@getNotification');

		//SEEN NOTIFICATION
		Route::get('seen-notification/{id}', 'Uitoux\EYatra\Api\DashboardController@saveNotification');
	});
});

Route::group(['middleware' => ['web']], function () {
	Route::get('eyatra/login', 'Uitoux\EYatra\AuthController@showLoginForm')->name('eyatraLoginForm');

	Route::group(['middleware' => ['auth']], function () {

		Route::post('/dems/dashboard/get-data', 'Uitoux\EYatra\DashboardController@getDEMSDashboardData')->name('getDEMSDashboardData');

		//ENTITIES
		Route::get('eyatra/entity/get-list-data/{entity_type_id?}', 'Uitoux\EYatra\EntityController@getEntityListData')->name('getEntityListData');
		Route::get('eyatra/entity/get-list', 'Uitoux\EYatra\EntityController@listEYatraEntity')->name('listEYatraEntity');
		Route::get('eyatra/entity/get-form-data/{entity_type_id}/{entity_id?}', 'Uitoux\EYatra\EntityController@eyatraEntityFormData')->name('eyatraEntityFormData');
		Route::post('eyatra/entity/save/{entity_type_id}', 'Uitoux\EYatra\EntityController@saveEYatraEntity')->name('saveEYatraEntity');
		Route::get('eyatra/entity/view/{entity_id}', 'Uitoux\EYatra\EntityController@viewEYatraEntity')->name('viewEYatraEntity');
		Route::get('eyatra/entity/delete/{entity_id}', 'Uitoux\EYatra\EntityController@deleteEYatraEntity')->name('deleteEYatraEntity');

		//ENTITIES NG
		Route::get('entity/get-form-data-ng/{entity_id?}', 'Uitoux\EYatra\RejectionController@eyatraEntityFormDataNg')->name('eyatraEntityFormDataNg');

		Route::get('entity/get-data-list', 'Uitoux\EYatra\RejectionController@listEYatraEntityNg')->name('listEYatraEntityNg');
		Route::post('entity/save/ng', 'Uitoux\EYatra\RejectionController@saveEYatraEntityNg')->name('saveEYatraEntityNg');
		Route::get('entity/delete/ng/{entity_id}', 'Uitoux\EYatra\RejectionController@deleteEYatraEntityNg')->name('deleteEYatraEntityNg');
		//END
		//TRAVEL MODE
		Route::get('eyatra/travel-mode/get-list', 'Uitoux\EYatra\TravelModeController@listEYatraTravelMode')->name('listEYatraTravelMode');
		Route::get('eyatra/travel-mode/get-form-data/{travel_mode_id?}', 'Uitoux\EYatra\TravelModeController@eyatraTravelModeFormData')->name('eyatraTravelModeFormData');
		Route::post('eyatra/travel-mode/save', 'Uitoux\EYatra\TravelModeController@saveEYatraTravelMode')->name('saveEYatraTravelMode');
		Route::get('eyatra/travel-mode/view/{entity_id}', 'Uitoux\EYatra\TravelModeController@viewEYatraTravelMode')->name('viewEYatraTravelMode');
		Route::get('eyatra/travel-mode/delete/{travel_mode_id}', 'Uitoux\EYatra\TravelModeController@deleteEYatraTravelMode')->name('deleteEYatraTravelMode');

		//LOCAL TRAVEL MODE
		Route::get('eyatra/local-travel-mode/get-list', 'Uitoux\EYatra\LocalTravelModeController@listEYatraLocalTravelMode')->name('listEYatraLocalTravelMode');
		Route::get('eyatra/local-travel-mode/get-form-data/{travel_mode_id?}', 'Uitoux\EYatra\LocalTravelModeController@eyatraLocalTravelModeFormData')->name('eyatraLocalTravelModeFormData');
		Route::post('eyatra/local-travel-mode/save', 'Uitoux\EYatra\LocalTravelModeController@saveEYatraLocalTravelMode')->name('saveEYatraLocalTravelMode');
		Route::get('eyatra/local-travel-mode/view/{entity_id}', 'Uitoux\EYatra\LocalTravelModeController@viewEYatraLocalTravelMode')->name('viewEYatraLocalTravelMode');
		Route::get('eyatra/local-travel-mode/delete/{travel_mode_id}', 'Uitoux\EYatra\LocalTravelModeController@deleteEYatraLocalTravelMode')->name('deleteEYatraLocalTravelMode');

		//COA NG
		Route::get('coa/get-form-data-ng/{entity_id?}', 'Uitoux\EYatra\CoaController@eyatraCoaFormDataNg')->name('eyatraCoaFormDataNg');

		Route::get('coa/get-data-list', 'Uitoux\EYatra\CoaController@listEYatraCoaNg')->name('listEYatraCoaNg');
		Route::post('coa/save/ng', 'Uitoux\EYatra\CoaController@saveEYatraCoaNg')->name('saveEYatraCoaNg');
		Route::get('coa/delete/ng/{entity_id}', 'Uitoux\EYatra\CoaController@deleteEYatraCoaNg')->name('deleteEYatraCoaNg');
		//END

		//GRADES
		Route::get('eyatra/grade/get-list', 'Uitoux\EYatra\GradeController@listEYatraGrade')->name('listEYatraGrade');
		Route::get('eyatra/grade/get-form-data/{grade_id?}', 'Uitoux\EYatra\GradeController@eyatraGradeFormData')->name('eyatraGradeFormData');
		Route::post('eyatra/grade/save', 'Uitoux\EYatra\GradeController@saveEYatraGrade')->name('saveEYatraGrade');
		Route::get('eyatra/grade/view/{grade_id}', 'Uitoux\EYatra\GradeController@viewEYatraGrade')->name('viewEYatraGrade');
		Route::get('eyatra/grade/delete/{grade_id}', 'Uitoux\EYatra\GradeController@deleteEYatraGrade')->name('deleteEYatraGrade');
		Route::get('eyatra/grade/filter', 'Uitoux\EYatra\GradeController@eyatraGradeFilter')->name('eyatraGradeFilter');

		//COA-CODES
		Route::get('eyatra/coa-code/get-list', 'Uitoux\EYatra\CoaCodeController@listEYatraCoaCode')->name('listEYatraCoaCode');
		Route::get('eyatra/coa-code/get-form-data/{coa_code_id?}', 'Uitoux\EYatra\CoaCodeController@eyatraCoaCodeFormData')->name('eyatraCoaCodeFormData');
		Route::post('eyatra/coa-code/save', 'Uitoux\EYatra\CoaCodeController@saveEYatraCoaCode')->name('saveEYatraCoaCode');
		Route::get('eyatra/coa-code/view/{coa_code_id}', 'Uitoux\EYatra\CoaCodeController@viewEYatraCoaCode')->name('viewEYatraCoaCode');
		Route::get('eyatra/coa-code/delete/{coa_code_id}', 'Uitoux\EYatra\CoaCodeController@deleteEYatraCoaCode')->name('deleteEYatraCoaCode');
		Route::get('eyatra/coa-code/filter', 'Uitoux\EYatra\CoaCodeController@eyatraCoaCodeFilter')->name('eyatraCoaCodeFilter');

		//AGENTS
		Route::get('eyatra/agent/get-list', 'Uitoux\EYatra\AgentController@listEYatraAgent')->name('listEYatraAgent');
		Route::get('eyatra/agent/get-form-data/{agent_id?}', 'Uitoux\EYatra\AgentController@eyatraAgentFormData')->name('eyatraAgentFormData');
		Route::post('eyatra/agent/save', 'Uitoux\EYatra\AgentController@saveEYatraAgent')->name('saveEYatraAgent');
		Route::get('eyatra/agent/view/{agent_id}', 'Uitoux\EYatra\AgentController@viewEYatraAgent')->name('viewEYatraAgent');
		Route::get('eyatra/agent/delete/{agent_id}', 'Uitoux\EYatra\AgentController@deleteEYatraAgent')->name('deleteEYatraAgent');

		Route::get('eyatra/agent/filter', 'Uitoux\EYatra\AgentController@eyatraAgentsfilter')->name('eyatraAgentsfilter');

		//STATES
		Route::get('eyatra/state/get-list', 'Uitoux\EYatra\StateController@listEYatraState')->name('listEYatraState');
		Route::get('eyatra/state/get-form-data/{state_id?}', 'Uitoux\EYatra\StateController@eyatraStateFormData')->name('eyatraStateFormData');
		Route::post('eyatra/state/save', 'Uitoux\EYatra\StateController@saveEYatraState')->name('saveEYatraState');
		Route::get('eyatra/state/view/{state_id}', 'Uitoux\EYatra\StateController@viewEYatraState')->name('viewEYatraState');
		Route::get('eyatra/state/delete/{state_id}', 'Uitoux\EYatra\StateController@deleteEYatraState')->name('deleteEYatraState');
		Route::get('eyatra/state/filter/', 'Uitoux\EYatra\StateController@filterEYatraState')->name('filterEYatraState');
		Route::post('eyatra/state/agent-search', 'Uitoux\EYatra\StateController@searchAgent')->name('searchAgent');

		//CITY
		Route::get('eyatra/city/get-list', 'Uitoux\EYatra\CityController@listEYatraCity')->name('listEYatraCity');
		Route::get('eyatra/city/get-form-data/{state_id?}', 'Uitoux\EYatra\CityController@eyatraCityFormData')->name('eyatraCityFormData');
		Route::post('eyatra/city/save', 'Uitoux\EYatra\CityController@saveEYatraCity')->name('saveEYatraCity');
		Route::get('eyatra/city/view/{city_id}', 'Uitoux\EYatra\CityController@viewEYatraCity')->name('viewEYatraCity');
		Route::get('eyatra/city/delete/{city_id}', 'Uitoux\EYatra\CityController@deleteEYatraCity')->name('deleteEYatraCity');
		Route::get('eyatra/city/get-filter-data', 'Uitoux\EYatra\CityController@eyatraCityFilterData')->name('eyatraCityFilterData');

		//Roles
		Route::get('eyatra/masters/role/get/list', 'Uitoux\EYatra\RoleController@getRolesList')->name('EyatragetRolesList');
		Route::get('eyatra/masters/roles/get-form-data/{id?}', 'Uitoux\EYatra\RoleController@editRolesAngular')->name('eyatraeditRolesAngular');
		Route::post('eyatra/masters/roles/save', 'Uitoux\EYatra\RoleController@saveRolesAngular')->name('EyatrasaveRolesAngular');
		// Route::get('/masters/role/add', 'Masters\RoleController@add')->name('addRoles');
		// Route::get('eyatra/masters/role/add', 'Uitoux\EYatra\OutletController@add')->name('addRole');
		// Route::get('eyatra/masters/role/edit/{id}', 'Uitoux\EYatra\OutletController@edit')->name('editRoles');
		// Route::post('eyatra/masters/role/save', 'Uitoux\EYatra\OutletController@save')->name('saveRoles');

		//OUTLETS
		Route::get('eyatra/outlet/get-list', 'Uitoux\EYatra\OutletController@listEYatraOutlet')->name('listEYatraOutlet');
		Route::get('eyatra/outlet/get-form-data/{outlet_id?}', 'Uitoux\EYatra\OutletController@eyatraOutletFormData')->name('eyatraOutletFormData');
		Route::post('eyatra/outlet/save', 'Uitoux\EYatra\OutletController@saveEYatraOutlet')->name('saveEYatraOutlet');
		Route::get('eyatra/outlet/view/{outlet_id}', 'Uitoux\EYatra\OutletController@viewEYatraOutlet')->name('viewEYatraOutlet');
		Route::get('eyatra/outlet/delete/{outlet_id}', 'Uitoux\EYatra\OutletController@deleteEYatraOutlet')->name('deleteEYatraOutlet');
		Route::post('eyatra/outlet/cashier-search', 'Uitoux\EYatra\OutletController@searchCashier')->name('searchCashier');
		Route::get('eyatra/lob/get-sbus', 'Uitoux\EYatra\LobController@getLobSbus')->name('getLobSbus');
		Route::get('eyatra/outlet/get-filter-data', 'Uitoux\EYatra\OutletController@eyatraOutletFilterData')->name('eyatraOutletFilterData');

		Route::get('/eyatra/outlet/filter/state-list/{id?}', 'Uitoux\EYatra\OutletController@stateFilterList')->name('outletGetStateFilterList');
		Route::get('/eyatra/outlet/filter/city-list/{id?}', 'Uitoux\EYatra\OutletController@cityFilterList')->name('outletGetCityFilterList');

		//EMPLOYEES
		Route::get('eyatra/employee/get-list', 'Uitoux\EYatra\EmployeeController@listEYatraEmployee')->name('listEYatraEmployee');
		Route::get('eyatra/employee/get-form-data/{employee_id?}', 'Uitoux\EYatra\EmployeeController@eyatraEmployeeFormData')->name('eyatraEmployeeFormData');
		Route::post('eyatra/employee/save', 'Uitoux\EYatra\EmployeeController@saveEYatraEmployee')->name('saveEYatraEmployee');
		Route::get('eyatra/employee/view/{employee_id}', 'Uitoux\EYatra\EmployeeController@viewEYatraEmployee')->name('viewEYatraEmployee');
		Route::get('eyatra/employee/delete/{employee_id}', 'Uitoux\EYatra\EmployeeController@deleteEYatraEmployee')->name('deleteEYatraEmployee');
		Route::post('eyatra/employee/manager/search', 'Uitoux\EYatra\EmployeeController@searchManager')->name('searchManager');
		Route::post('eyatra/employee/get/sbu', 'Uitoux\EYatra\EmployeeController@getSbuByLob')->name('getSbuByLob');
		Route::get('eyatra/employee/filter', 'Uitoux\EYatra\EmployeeController@filterEYatraEmployee')->name('filterEYatraEmployee');
		Route::post('eyatra/employee/get-designation', 'Uitoux\EYatra\EmployeeController@getDesignationByGrade')->name('getDesignationByGrade');

		//EMPLOYEES IMPORT
		Route::get('import-employee/list', 'Uitoux\EYatra\EmployeeController@getImportJobsList')->name('getImportJobsList');
		Route::post('/import-job/update-job-status', 'Uitoux\EYatra\EmployeeController@update_import_jobs_status')->name('updateImportJobsStatus');
		Route::post('/import-employee/save', 'Uitoux\EYatra\EmployeeController@saveImportJobs')->name('saveImportJobs');
		Route::get('eyatra/import/type/get', 'Uitoux\EYatra\EmployeeController@getImportFormData')->name('getImportFormData');

		//END

		//EXPENSE VOUCHER ADVANCE
		Route::get('eyatra/expense/voucher-advance/list', 'Uitoux\EYatra\ExpenseVoucherAdvanceController@listExpenseVoucherRequest')->name('listExpenseVoucherRequest');
		Route::get('eyatra/expense/voucher-advance/get-form-data/{id?}', 'Uitoux\EYatra\ExpenseVoucherAdvanceController@expenseVoucherFormData')->name('expenseVoucherFormData');
		Route::post('eyatra/expense/voucher-advance/save', 'Uitoux\EYatra\ExpenseVoucherAdvanceController@expenseVoucherSave')->name('expenseVoucherSave');
		Route::get('eyatra/expense/voucher-advance/view/{id?}', 'Uitoux\EYatra\ExpenseVoucherAdvanceController@expenseVoucherView')->name('expenseVoucherView');
		Route::get('eyatra/expense/voucher-advance/delete/{id?}', 'Uitoux\EYatra\ExpenseVoucherAdvanceController@expenseVoucherDelete')->name('expenseVoucherDelete');
		Route::get('eyatra/expense/voucher-advance/filter-data', 'Uitoux\EYatra\ExpenseVoucherAdvanceController@ExpenseVoucherAdvanceFilterData')->name('ExpenseVoucherAdvanceFilterData');

		//EXPENSE VOUCHER ADVANCE VERIFICATION MANAGER
		Route::get('eyatra/expense/voucher-advance/verification/list', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerificationController@listExpenseVoucherverificationRequest')->name('listExpenseVoucherverificationRequest');
		Route::post('eyatra/expense/voucher-advance/verification/save', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerificationController@expenseVoucherVerificationSave')->name('expenseVoucherVerificationSave');
		Route::get('eyatra/expense/voucher-advance/verification/view/{id?}', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerificationController@expenseVoucherVerificationView')->name('expenseVoucherVerificationView');

		//EXPENSE VOUCHER ADVANCE VERIFICATION CASHIER
		Route::get('eyatra/expense/voucher-advance/verification2/list', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerification2Controller@listExpenseVoucherverification2Request')->name('listExpenseVoucherverification2Request');
		Route::post('eyatra/expense/voucher-advance/verification2/save', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerification2Controller@expenseVoucherVerification2Save')->name('expenseVoucherVerification2Save');
		Route::get('eyatra/expense/voucher-advance/verification2/view/{id?}', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerification2Controller@expenseVoucherVerification2View')->name('expenseVoucherVerification2View');

		//EXPENSE VOUCHER ADVANCE VERIFICATION FINANCIER
		Route::get('eyatra/expense/voucher-advance/verification3/list', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerification3Controller@listExpenseVoucherverification3Request')->name('listExpenseVoucherverification3Request');
		Route::post('eyatra/expense/voucher-advance/verification3/save', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerification3Controller@expenseVoucherVerification3Save')->name('expenseVoucherVerification3Save');
		Route::get('eyatra/expense/voucher-advance/verification3/view/{id?}', 'Uitoux\EYatra\ExpenseVoucherAdvanceVerification3Controller@expenseVoucherVerification3View')->name('expenseVoucherVerification3View');

		//DESIGNATIONS
		Route::get('eyatra/designations/get-list', 'Uitoux\EYatra\DesignationController@listEYatraDesignation')->name('listEYatraDesignations');
		Route::get('eyatra/designations/get-form-data/{designation_id?}', 'Uitoux\EYatra\DesignationController@eyatraDesignationFormData')->name('eyatraDesignationFormData');
		Route::post('eyatra/designation/save', 'Uitoux\EYatra\DesignationController@saveEYatraDesignation')->name('saveEYatraDesignation');
		Route::get('eyatra/designation/view/{designation_id}', 'Uitoux\EYatra\DesignationController@viewEYatraDesignation')->name('viewEYatraDesignation');
		Route::get('eyatra/designation/delete/{designation_id}', 'Uitoux\EYatra\DesignationController@deleteEYatraDesignation')->name('deleteEYatraDesignation');

		//REGIONS
		Route::get('eyatra/region/get-list', 'Uitoux\EYatra\RegionController@listEYatraRegion')->name('listEYatraRegion');
		Route::get('eyatra/region/get-form-data/{region_id?}', 'Uitoux\EYatra\RegionController@eyatraRegionFormData')->name('eyatraRegionFormData');
		Route::post('eyatra/region/save', 'Uitoux\EYatra\RegionController@saveEYatraRegion')->name('saveEYatraRegion');
		Route::get('eyatra/region/view/{region_id}', 'Uitoux\EYatra\RegionController@viewEYatraRegion')->name('viewEYatraRegion');
		Route::get('eyatra/region/delete/{region_id}', 'Uitoux\EYatra\RegionController@deleteEYatraRegion')->name('deleteEYatraRegion');
		Route::post('eyatra/region/get/state', 'Uitoux\EYatra\RegionController@getStateByCountry')->name('getStateByCountry');
		Route::get('eyatra/region/get-filter-data', 'Uitoux\EYatra\RegionController@eyatraRegionFilterData')->name('eyatraRegionFilterData');

		//TRIPS
		Route::get('eyatra/trip/get-list', 'Uitoux\EYatra\TripController@listTrip')->name('listTrip');
		Route::get('eyatra/trip/get-form-data/{trip_id?}', 'Uitoux\EYatra\TripController@tripFormData')->name('tripFormData');
		Route::post('eyatra/trip/save', 'Uitoux\EYatra\TripController@saveTrip')->name('saveTrip');
		Route::get('eyatra/trip/view/{trip_id}', 'Uitoux\EYatra\TripController@viewTrip')->name('viewTrip');
		Route::get('eyatra/trip/delete/{trip_id}', 'Uitoux\EYatra\TripController@deleteTrip')->name('deleteTrip');
		Route::get('eyatra/trip/cancel/{trip_id}', 'Uitoux\EYatra\TripController@cancelTrip')->name('cancelTrip');
		Route::get('eyatra/trip/verification-request/{trip_id}', 'Uitoux\EYatra\TripController@tripVerificationRequest')->name('tripVerificationRequest');
		Route::get('/eyatra/trip/visit/booking-cancel/{visit_id}', 'Uitoux\EYatra\TripController@cancelTripVisitBooking')->name('cancelTripVisitBooking');
		Route::get('eyatra/trips/visit/get-form-data/{trip_id}', 'Uitoux\EYatra\TripController@visitFormData')->name('visitFormData');
		Route::get('/eyatra/trip/visit/request-booking-cancel/{visit_id}', 'Uitoux\EYatra\TripController@requestCancelVisitBooking')->name('requestCancelVisitBooking');
		Route::get('eyatra/trips/visit/delete/{visit_id}', 'Uitoux\EYatra\TripController@deleteVisit')->name('deleteVisit');
		// Route::post('eyatra/trip/city/search', 'Uitoux\EYatra\TripController@searchCity')->name('searchCity');
		Route::post('eyatra/trip/city/search', 'Uitoux\EYatra\CityController@searchCity')->name('searchCity');
		Route::get('eyatra/trip/get-filter-data', 'Uitoux\EYatra\TripController@eyatraTripFilterData')->name('eyatraTripFilterData');

		//TRIPS VERIFICATION
		Route::get('eyatra/trip/verification/get-list', 'Uitoux\EYatra\TripVerificationController@listTripVerification')->name('listTripVerification');
		Route::get('eyatra/trip/verification/get-form-data/{trip_id?}', 'Uitoux\EYatra\TripVerificationController@tripVerificationFormData')->name('tripVerificationFormData');
		Route::post('eyatra/trip/verification/save', 'Uitoux\EYatra\TripVerificationController@saveTripVerification')->name('saveTripVerification');

		Route::get('eyatra/trip/verification1/get-filter-data', 'Uitoux\EYatra\TripVerificationController@eyatraTripVerificationFilterData')->name('eyatraTripVerificationFilterData');
		Route::post('eyatra/trip/verification/approve', 'Uitoux\EYatra\TripVerificationController@approveTrip')->name('approveTripVerification');
		Route::post('eyatra/trip/verification/reject', 'Uitoux\EYatra\TripVerificationController@rejectTrip')->name('rejectTripVerification');

		// FINANCE EMPLOYEE CLAIMS
		Route::get('eyatra/finance-emp/claims/get-list', 'Uitoux\EYatra\FinanceEmployeeClaimController@listEYatraFinanceEmployeeClaim')->name('listEYatraFinanceEmployeeClaim');
		Route::get('eyatra/finance-emp/claims/get-form-data/{trip_id?}', 'Uitoux\EYatra\FinanceEmployeeClaimController@eyatraFinanceEmployeeClaimFormData')->name('eyatraFinanceEmployeeClaimFormData');

		//FINANCE AGENT CLAIMS
		Route::get('eyatra/finance/agent/claim/get-list', 'Uitoux\EYatra\AgentClaimController@listFinanceEYatraAgentClaimList')->name('listFinanceEYatraAgentClaimList');
		Route::get('eyatra/finance/agent/claim/view/{agent_claim_id}', 'Uitoux\EYatra\AgentClaimController@viewEYatraFinanceAgentClaim')->name('viewEYatraFinanceAgentClaim');
		Route::post('eyatra/finance/agent-claim/payment', 'Uitoux\EYatra\AgentClaimController@payAgentClaimRequest')->name('payAgentClaimRequest');
		Route::post('eyatra/finance/agent-claim/reject', 'Uitoux\EYatra\AgentClaimController@rejectAgentClaimRequest')->name('rejectAgentClaimRequest');

		//ADVANCE CLAIM REQUESTS
		Route::get('eyatra/advance-claim/request/get-list', 'Uitoux\EYatra\AdvanceClaimRequestController@listAdvanceClaimRequest')->name('listAdvanceClaimRequest');
		Route::get('eyatra/advance-claim/request/get-form-data/{trip_id?}', 'Uitoux\EYatra\AdvanceClaimRequestController@advanceClaimRequestFormData')->name('advanceClaimRequestFormData');
		Route::post('eyatra/advance-claim/request/save', 'Uitoux\EYatra\AdvanceClaimRequestController@saveAdvanceClaimRequest')->name('saveAdvanceClaimRequest');
		Route::get('eyatra/advance-claim/request/approve/{trip_id}', 'Uitoux\EYatra\AdvanceClaimRequestController@approveAdvanceClaimRequest')->name('approveAdvanceClaimRequest');
		Route::post('eyatra/advance-claim/request/reject', 'Uitoux\EYatra\AdvanceClaimRequestController@rejectAdvanceClaimRequest')->name('rejectAdvanceClaimRequest');
		Route::get('eyatra/trip/verification/get-filter-data', 'Uitoux\EYatra\AdvanceClaimRequestController@eyatraAdvanceClaimFilterData')->name('eyatraAdvanceClaimFilterData');
		Route::get('eyatra/advance-claim/request/export', 'Uitoux\EYatra\AdvanceClaimRequestController@AdvanceClaimRequestExport')->name('AdvanceClaimRequestExport');
		Route::post('eyatra/advance-claim/request/multiple/approve', 'Uitoux\EYatra\AdvanceClaimRequestController@AdvanceClaimRequestApprove')->name('AdvanceClaimRequestApprove');

		//AGENT REQUESTS
		Route::get('eyatra/agent/request/get-list', 'Uitoux\EYatra\AgentRequestController@listAgentRequest')->name('listAgentRequest');
		Route::get('eyatra/agent/request/get-form-data/{trip_id?}', 'Uitoux\EYatra\AgentRequestController@agentRequestFormData')->name('agentRequestFormData');
		Route::post('eyatra/agent/request/save', 'Uitoux\EYatra\AgentRequestController@saveAgentRequest')->name('saveAgentRequest');

		Route::get('eyatra/financier/request/get-form-data/{trip_id?}', 'Uitoux\EYatra\AgentRequestController@financierRequestFormData')->name('financierRequestFormData');

		//TRIPS BOOKING REQUESTS
		Route::get('eyatra/trips/booking-requests/get-list', 'Uitoux\EYatra\TripBookingRequestController@listTripBookingRequests')->name('listTripBookingRequests');
		Route::get('eyatra/trips/booking-requests/get-view-data/{visit_id?}', 'Uitoux\EYatra\TripBookingRequestController@tripBookingRequestsViewData')->name('tripBookingUpdatesFormData');
		Route::get('eyatra/trips/booking-requests/filter', 'Uitoux\EYatra\TripBookingRequestController@filterEYatraTripBookingRequests')->name('filterEYatraTripBookingRequests');

		//AGENT CLAIMS
		Route::get('eyatra/agent/claim/get-list', 'Uitoux\EYatra\AgentClaimController@listEYatraAgentClaimList')->name('listEYatraAgentClaimList');
		Route::get('eyatra/agent/claim/add/{agent_claim_id?}', 'Uitoux\EYatra\AgentClaimController@eyatraAgentClaimFormData')->name('eyatraAgentClaimFormData');
		Route::post('eyatra/agent/claim/save', 'Uitoux\EYatra\AgentClaimController@saveEYatraAgentClaim')->name('saveEYatraAgentClaim');
		Route::get('eyatra/agent/claim/view/{agent_claim_id}', 'Uitoux\EYatra\AgentClaimController@viewEYatraAgentClaim')->name('viewEYatraAgentClaim');
		Route::get('eyatra/agent/claim/delete/{agent_claim_id}', 'Uitoux\EYatra\AgentClaimController@deleteEYatraAgentClaim')->name('deleteEYatraAgentClaim');
		Route::get('eyatra/agent/claim/filter_datas', 'Uitoux\EYatra\AgentClaimController@filterData')->name('agentClaim_filter_data');

		Route::get('eyatra/booking/view/get-form-data/{trip_id?}', 'Uitoux\EYatra\AgentClaimController@bookingViewFormData')->name('bookingViewFormData');

		//TRIP CLAIM
		Route::get('eyatra/trip/claim/get-list', 'Uitoux\EYatra\TripClaimController@listEYatraTripClaimList')->name('listEYatraTripClaimList');
		Route::get('eyatra/trip/claim/get-form-data/{trip_id?}', 'Uitoux\EYatra\TripClaimController@eyatraTripClaimFormData')->name('eyatraTripClaimFormData');
		Route::post('eyatra/trip/claim/save', 'Uitoux\EYatra\TripClaimController@saveEYatraTripClaim')->name('saveEYatraTripClaim');
		Route::post('eyatra/trip/claim/get-expense-data', 'Uitoux\EYatra\TripClaimController@eyatraTripExpenseData')->name('eyatraTripExpenseData');
		Route::get('eyatra/trip/claim/view/{trip_id?}', 'Uitoux\EYatra\TripClaimController@viewEYatraTripClaim')->name('viewEYatraTripClaim');
		Route::get('eyatra/trip/claim/delete/{trip_id}', 'Uitoux\EYatra\TripClaimController@deleteEYatraTripClaim')->name('deleteEYatraTripClaim');
		Route::get('eyatra/trip/claim/get-eligible-amount', 'Uitoux\EYatra\TripClaimController@getEligibleAmtBasedonCitycategoryGrade')->name('getEligibleAmtBasedonCitycategoryGrade');
		Route::get('eyatra/trip/claim/get-eligible-amount/by-staytype', 'Uitoux\EYatra\TripClaimController@getEligibleAmtBasedonCitycategoryGradeStaytype')->name('getEligibleAmtBasedonCitycategoryGradeStaytype');
		Route::get('eyatra/trip/claim/get-visit-transport-mode-claim-status', 'Uitoux\EYatra\TripClaimController@getVisitTrnasportModeClaimStatus')->name('getVisitTrnasportModeClaimStatus');
		Route::get('eyatra/trip/claim/get-filter-data', 'Uitoux\EYatra\TripClaimController@eyatraTripClaimFilterData')->name('eyatraTripClaimFilterData');

		//TRIP CLAIM VERIFICATION ONE
		Route::get('eyatra/trip/claim/verification/one/get-list', 'Uitoux\EYatra\TripClaimVerificationOneController@listEYatraTripClaimVerificationOneList')->name('listEYatraTripClaimVerificationOneList');
		Route::get('eyatra/trip/claim/verification/one/view/{trip_id?}', 'Uitoux\EYatra\TripClaimVerificationOneController@viewEYatraTripClaimVerificationOne')->name('viewEYatraTripClaimVerificationOne');
		Route::post('eyatra/trip/claim/verification/one/reject', 'Uitoux\EYatra\TripClaimVerificationOneController@rejectTripClaimVerificationOne')->name('rejectTripClaimVerificationOne');
		Route::get('eyatra/trip/claim/verification/one/approve/{trip_id}', 'Uitoux\EYatra\TripClaimVerificationOneController@approveTripClaimVerificationOne')->name('approveTripClaimVerificationOne');

		//TRIP CLAIM VERIFICATION TWO
		Route::get('eyatra/trip/claim/verification/two/get-list', 'Uitoux\EYatra\TripClaimVerificationTwoController@listEYatraTripClaimVerificationTwoList')->name('listEYatraTripClaimVerificationTwoList');
		Route::get('eyatra/trip/claim/verification/two/view/{trip_id?}', 'Uitoux\EYatra\TripClaimVerificationTwoController@viewEYatraTripClaimVerificationTwo')->name('viewEYatraTripClaimVerificationTwo');
		Route::post('eyatra/trip/claim/verification/two/reject', 'Uitoux\EYatra\TripClaimVerificationTwoController@rejectTripClaimVerificationTwo')->name('rejectTripClaimVerificationTwo');
		Route::get('eyatra/trip/claim/verification/two/approve/{trip_id}', 'Uitoux\EYatra\TripClaimVerificationTwoController@approveTripClaimVerificationTwo')->name('approveTripClaimVerificationTwo');

		//TRIP CLAIM VERIFICATION THREE
		Route::get('eyatra/trip/claim/verification/three/get-list', 'Uitoux\EYatra\TripClaimVerificationThreeController@listEYatraTripClaimVerificationThreeList')->name('listEYatraTripClaimVerificationThreeList');
		Route::get('eyatra/trip/claim/verification/three/view/{trip_id?}', 'Uitoux\EYatra\TripClaimVerificationThreeController@viewEYatraTripClaimVerificationThree')->name('viewEYatraTripClaimVerificationThree');
		Route::post('eyatra/trip/claim/verification/three/reject', 'Uitoux\EYatra\TripClaimVerificationThreeController@rejectTripClaimVerificationThree')->name('rejectTripClaimVerificationThree');
		Route::post('eyatra/trip/claim/verification/three/approve', 'Uitoux\EYatra\TripClaimVerificationThreeController@approveTripClaimVerificationThree')->name('approveTripClaimVerificationThree');
		Route::post('eyatra/trip/claim/hold', 'Uitoux\EYatra\TripClaimVerificationThreeController@holdTripClaimVerificationThree')->name('holdTripClaimVerificationThree');
		Route::get('eyatra/trip/claim/verification/three/financier-approve/{trip_id?}', 'Uitoux\EYatra\TripClaimVerificationThreeController@approveFinancierTripClaimVerification')->name('approveFinancierTripClaimVerification');

		//TRIP CLAIM PAYMENT PENDING FOR EMPLOYEE
		Route::get('eyatra/trip/claim/payment-pending/list', 'Uitoux\EYatra\TripClaimPendingController@listEYatraTripClaimPaymentPendingList')->name('listEYatraTripClaimPaymentPendingList');

		Route::post('eyatra/employee-claim/request/multiple/approve', 'Uitoux\EYatra\TripClaimPendingController@EmployeeClaimPaymentPendingApprove')->name('EmployeeClaimPaymentPendingApprove');
		Route::post('eyatra/employee-claim/request/single/approve', 'Uitoux\EYatra\TripClaimPendingController@EmployeeClaimPaymentPendingSingleApprove')->name('EmployeeClaimPaymentPendingSingleApprove');

		//TRIPS BOOKING UPDATES
		Route::get('eyatra/trips/booking-updates/get-list', 'Uitoux\EYatra\TripBookingUpdateController@listTripBookingUpdates')->name('listTripBookingUpdates');
		Route::get('eyatra/trips/booking-updates/get-form-data/{visit_id?}', 'Uitoux\EYatra\TripBookingUpdateController@tripBookingUpdatesFormData')->name('tripBookingUpdatesFormData');
		Route::post('eyatra/trips/booking-updates/save', 'Uitoux\EYatra\TripBookingUpdateController@saveTripBookingUpdates')->name('saveTripBookingUpdates');
		Route::get('/eyatra/trip/booking-updates/booking-tatkal-change/{visit_id}', 'Uitoux\EYatra\TripBookingUpdateController@changeBookingTatkal')->name('changeBookingTatkal');

		//TRIPS TATKAL BOOKING
		Route::get('eyatra/trips/tatkal/booking-requests/get-list', 'Uitoux\EYatra\TripBookingRequestController@listTripTatkalBookingRequests')->name('listTripTatkalBookingRequests');
		Route::get('eyatra/trips/tatkal/booking-requests/get-view-data/{visit_id?}', 'Uitoux\EYatra\TripBookingRequestController@tripBookingRequestsViewData')->name('tripBookingUpdatesFormData');

		//OUTLET REIMPURSEMENT
		Route::get('eyatra/outlet-reimpursement/get-list', 'Uitoux\EYatra\OutletReimpursementController@listOutletReimpursement')->name('listOutletReimpursement');
		Route::get('eyatra/outlet-reimpursement/view/{outlet_id}', 'Uitoux\EYatra\OutletReimpursementController@viewEYatraOutletReimpursement')->name('viewEYatraOutletReimpursement');
		Route::post('eyatra/outlet-reimpursement/cash/topup', 'Uitoux\EYatra\OutletReimpursementController@cashTopUp')->name('cashTopUp');

		//OUTLET REIMPURSEMENT
		Route::get('eyatra/outlet-reimpursement/cashier/get-list', 'Uitoux\EYatra\OutletReimpursementController@listCashierOutletReimpursement')->name('listCashierOutletReimpursement');

		//HELPERS
		Route::post('eyatra/city/search', 'Uitoux\EYatra\CityController@searchCity')->name('searchCity');
		Route::post('eyatra/state/get', 'Uitoux\EYatra\StateController@getStateList')->name('getStateList');
		Route::post('eyatra/city/get', 'Uitoux\EYatra\CityController@getCityList')->name('getCityList');

		//PETTY CASH
		Route::get('eyatra/petty-cash/request/get-list/', 'Uitoux\EYatra\PettyCashController@listPettyCashRequest')->name('listPettyCashRequest');
		Route::get('eyatra/petty-cash/request/get-form-data/{type_id?}/{pettycash_id?}', 'Uitoux\EYatra\PettyCashController@pettycashFormData')->name('pettycashFormData');
		Route::post('eyatra/petty-cash/request/save', 'Uitoux\EYatra\PettyCashController@pettycashSave')->name('pettycashSave');
		Route::get('eyatra/petty-cash/request/delete/{type_id}/{pettycash_id}', 'Uitoux\EYatra\PettyCashController@pettycashDelete')->name('pettycashDelete');
		Route::get('eyatra/petty-cash/request/view/{type_id}/{pettycash_id}', 'Uitoux\EYatra\PettyCashController@pettycashView')->name('pettycashView');
		Route::get('eyatra/petty-cash/request/filter-data', 'Uitoux\EYatra\PettyCashController@pettycashFilterData')->name('pettycashFilterData');
		Route::post('eyatra/petty-cash/searchEmployee', 'Uitoux\EYatra\PettyCashController@searchEmployee')->name('searchEmployee');
		Route::get('eyatra/petty-cash/fillemployee/{id}', 'Uitoux\EYatra\PettyCashController@fillEmployee')->name('fillEmployee');

		//PETTY CASH VERIFICATION VIEW FOR MANAGER
		Route::get('eyatra/petty-cash/manager/get-list/', 'Uitoux\EYatra\PettyCashManagerVerificationController@listPettyCashVerificationManager')->name('listPettyCashVerificationManager');
		Route::post('eyatra/petty-cash/manager/approve/save', 'Uitoux\EYatra\PettyCashManagerVerificationController@pettycashManagerVerificationSave')->name('pettycashManagerVerificationSave');
		Route::get('eyatra/petty-cash/manager/view/{type_id}/{pettycash_id}', 'Uitoux\EYatra\PettyCashManagerVerificationController@pettycashManagerVerificationView')->name('pettycashManagerVerificationView');

		//PETTY CASH VERIFICATION VIEW FOR FINANCE
		Route::get('eyatra/petty-cash/finance/get-list/', 'Uitoux\EYatra\PettyCashFinanceVerificationController@listPettyCashVerificationFinance')->name('listPettyCashVerificationFinance');
		Route::post('eyatra/petty-cash/finance/save', 'Uitoux\EYatra\PettyCashFinanceVerificationController@pettycashFinanceVerificationSave')->name('pettycashFinanceVerificationSave');
		Route::get('eyatra/petty-cash/finance/view/{type_id}/{pettycash_id}', 'Uitoux\EYatra\PettyCashFinanceVerificationController@pettycashFinanceVerificationView')->name('pettycashFinanceVerificationView');

		//PETTY CASH VERIFICATION VIEW FOR CASHIER
		Route::get('eyatra/petty-cash/cashier/get-list/', 'Uitoux\EYatra\PettyCashCashierVerificationController@listPettyCashVerificationCashier')->name('listPettyCashVerificationCashier');
		Route::post('eyatra/petty-cash/cashier/save', 'Uitoux\EYatra\PettyCashCashierVerificationController@pettycashCashierVerificationSave')->name('pettycashCashierVerificationSave');
		Route::get('eyatra/petty-cash/cashier/view/{type_id}/{pettycash_id}', 'Uitoux\EYatra\PettyCashCashierVerificationController@pettycashCashierVerificationView')->name('pettycashCashierVerificationView');

		//ALTERNATE APPROVE LIST
		Route::get('eyatra/alternate-approve/request/get-list', 'Uitoux\EYatra\AlternateApproveController@listAlternateApproveRequest')->name('listAlternateApproveRequest');
		Route::get('eyatra/alternate-approve/request/get-form-data/{alternate_id?}', 'Uitoux\EYatra\AlternateApproveController@alternateapproveFormData')->name('alternateapproveFormData');
		Route::post('eyatra/alternate-approve/request/save', 'Uitoux\EYatra\AlternateApproveController@alternateapproveSave')->name('alternateapproveSave');
		Route::post('eyatra/alternate-approve/manager', 'Uitoux\EYatra\AlternateApproveController@getmanagerList')->name('getmanagerList');
		Route::get('eyatra/alternate-approve/request/delete/{alternate_id}', 'Uitoux\EYatra\AlternateApproveController@alternateapproveDelete')->name('alternateapproveDelete');

		//LOCAL TRIP
		Route::get('eyatra/local-trip/get-list', 'Uitoux\EYatra\LocalTripController@listLocalTrip')->name('listLocalTrip');
		Route::get('eyatra/local-trip/get-form-data/{trip_id?}', 'Uitoux\EYatra\LocalTripController@localTripFormData')->name('localTripFormData');
		Route::post('eyatra/local-trip/save', 'Uitoux\EYatra\LocalTripController@saveLocalTrip')->name('saveLocalTrip');
		Route::get('eyatra/local-trip/view/{trip_id}', 'Uitoux\EYatra\LocalTripController@viewLocalTrip')->name('viewLocalTrip');
		Route::get('eyatra/local-trip/get-filter-data', 'Uitoux\EYatra\LocalTripController@eyatraLocalTripFilterData')->name('eyatraLocalTripFilterData');
		Route::get('eyatra/local-trip/delete/{trip_id}', 'Uitoux\EYatra\LocalTripController@deleteTrip')->name('deleteTrip');

		//LOCAL TRIP CLAIM
		Route::get('eyatra/local-trip/claim/get-list', 'Uitoux\EYatra\LocalTripController@listClaimedLocalTrip')->name('listClaimedLocalTrip');

		//LOCAL TRIP MANAGER VERIFICATION
		Route::get('eyatra/local-trip/verification/get-list', 'Uitoux\EYatra\LocalTripController@listLocalTripVerification')->name('listLocalTripVerification');
		Route::get('eyatra/local-trip/verification/approve/{trip_id?}', 'Uitoux\EYatra\LocalTripController@approveLocalTrip')->name('approveLocalTrip');
		Route::post('eyatra/local-trip/verification/reject', 'Uitoux\EYatra\LocalTripController@rejectLocalTrip')->name('rejectLocalTrip');
		Route::get('eyatra/local-trip/verification/get-filter-data', 'Uitoux\EYatra\LocalTripController@eyatraLocalTripVerificationFilterData')->name('eyatraLocalTripVerificationFilterData');

		//LOCAL TRIP FINANCIER VERIFICATION
		Route::get('eyatra/local-trip/financier/verification/get-list', 'Uitoux\EYatra\LocalTripController@listFinancierLocalTripVerification')->name('listFinancierLocalTripVerification');
		Route::post('eyatra/local-trip/financier/verification/approve', 'Uitoux\EYatra\LocalTripController@financierApproveLocalTrip')->name('financierApproveLocalTrip');
		Route::post('eyatra/local-trip/financier/verification/hold', 'Uitoux\EYatra\LocalTripController@financierHoldLocalTrip')->name('financierHoldLocalTrip');
		Route::post('eyatra/local-trip/financier/verification/reject', 'Uitoux\EYatra\LocalTripController@financierRejectLocalTrip')->name('financierRejectLocalTrip');
		Route::get('eyatra/local-trip/financier/verification/get-filter-data', 'Uitoux\EYatra\LocalTripController@eyatraLocalTripFinancierVerificationFilterData')->name('eyatraLocalTripFinancierVerificationFilterData');

		//REPORT
		//OUTSTATION TRIP REPORT
		Route::get('eyatra/report/outstation-trip/get-filter-data', 'Uitoux\EYatra\ReportController@eyatraOutstationFilterData')->name('eyatraOutstationFilterData');
		Route::get('eyatra/report/outstation-trip/get-list', 'Uitoux\EYatra\ReportController@listOutstationTripReport')->name('listOutstationTripReport');
		Route::get('eyatra/report/outstation-trip/export', 'Uitoux\EYatra\ReportController@outstationTripExport')->name('outstationTripExport');
		Route::post('eyatra/employee/get-list', 'Uitoux\EYatra\ReportController@getEmployeeByOutlet')->name('getEmployeeByOutlet');

		//LOCAL TRIP REPORT
		Route::get('eyatra/report/local-trip/get-filter-data', 'Uitoux\EYatra\ReportController@eyatraLocalFilterData')->name('eyatraLocalFilterData');
		Route::get('eyatra/report/local-trip/get-list', 'Uitoux\EYatra\ReportController@listLocalTripReport')->name('listLocalTripReport');
		Route::get('eyatra/report/local-trip/export', 'Uitoux\EYatra\ReportController@localTripExport')->name('localTripExport');
	});
});