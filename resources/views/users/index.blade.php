@extends('layouts.app')
<style>
    .dataTables_processing {
        display: none !important;
    }
</style>
@section('content')

<div class="page-wrapper">

    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.user_plural')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.user_table')}}</li>
            </ol>
        </div>
        <div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-body">
                        <div id="data-table_processing" class="dataTables_processing panel panel-default"
                            style="display: none;">{{trans('lang.processing')}}
                        </div>
                        <div class="table-responsive m-t-10">
                            <table id="userTable"
                                class="display nowrap table table-hover table-striped table-bordered table table-striped"
                                cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <?php if (in_array('user.delete', json_decode(@session('user_permissions')))) { ?>
                                            <th class="delete-all"><input type="checkbox" id="is_active"><label
                                                    class="col-3 control-label" for="is_active"><a id="deleteAll"
                                                        class="do_not_delete" href="javascript:void(0)"><i
                                                            class="fa fa-trash"></i> {{trans('lang.all')}}</a></label></th>

                                        <?php } ?>


                                        <th>{{trans('lang.extra_image')}}</th>
                                        <th>{{trans('lang.user_name')}}</th>
                                        <th>{{trans('lang.email')}}</th>
                                        <th>{{trans('lang.phone')}}</th>
                                        <th>{{trans('lang.date')}}</th>
                                        <th>{{trans('lang.active')}}</th>
                                        <th>{{trans('lang.dashboard_total_orders')}}</th>

                                        <!-- <th >{{trans('lang.wallet_transaction')}}</th> -->
                                        <!-- <th >{{trans('lang.role')}}</th> -->

                                        <th>{{trans('lang.actions')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

@endsection

@section('scripts')


<script type="text/javascript">

    var database = firebase.firestore();
    var defaultImg = "{{ asset('/images/default_user.png') }}";

    var ref = database.collection('users')/* .orderBy('createdAt', 'desc') */;

    var user_permissions = '<?php echo @session('user_permissions') ?>';

    user_permissions = JSON.parse(user_permissions);

    var checkDeletePermission = false;

    if ($.inArray('user.delete', user_permissions) >= 0) {
        checkDeletePermission = true;
    }

    var placeholderImage = '';

    var deleteMsg = "{{trans('lang.delete_alert')}}";
    var deleteSelectedRecordMsg = "{{trans('lang.selected_delete_alert')}}";
    $(document).ready(function () {
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        jQuery("#overlay").show();
        // Initialize DataTable

        const table = $('#userTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: true, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            ajax: function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = (checkDeletePermission) ? ['', '', 'fullName', 'email', 'phone', 'createdAt', '', 'total_rides'] : ['', 'fullName', 'email', 'countryCode', 'createdAt', '', 'total_rides']; // Ensure this matches the actual column names
                const orderByField = orderableColumns[orderColumnIndex]; // Adjust the index to match your table

                if (searchValue.length >= 3 || searchValue.length === 0) {
                    $('#overlay').show();
                }

                ref.get().then(async function (querySnapshot) {
                    if (querySnapshot.empty) {
                        console.error("No data found in Firestore.");
                        $('#overlay').hide(); // Hide loader
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: [] // No data
                        });
                        return;
                    }

                    let records = [];
                    let filteredRecords = [];

                    querySnapshot.forEach(function (doc) {
                        let childData = doc.data();
                        childData.id = doc.id; // Ensure the document ID is included in the data
                        if(childData.countryCode != undefined && childData.countryCode != null) {
                        childData.phone = '+' + (childData.countryCode.includes('+') ? childData.countryCode.slice(1) : childData.countryCode) + '-' + childData.phoneNumber;
                        }
                        if (searchValue) {
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdAt")) {
                                try {
                                    date = childData.createdAt.toDate().toDateString();
                                    time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                                } catch (err) {
                                }
                            }
                            var createdAt = date + ' ' + time;

                            if (
                                (childData.fullName && childData.fullName.toString().includes(searchValue)) ||
                                (createdAt && createdAt.toString().toLowerCase().includes(searchValue)) ||
                                (childData.email && childData.email.toString().includes(searchValue)) ||
                                (childData.phone && childData.phone.toString().includes(searchValue))

                            ) {
                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    });

                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] /* ? a[orderByField].toString().toLowerCase() : '' */;
                        let bValue = b[orderByField] /* ? b[orderByField].toString().toLowerCase() : '' */;
                        if (orderByField === 'createdAt') {
                            aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                            bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                        } else if (orderByField === 'total_rides') {
                            aValue = a[orderByField] ? parseFloat(a[orderByField]) : 0.0;
                            bValue = b[orderByField] ? parseFloat(b[orderByField]) : 0.0;
                        }
                        if (orderDirection === 'asc') {
                            return (aValue > bValue) ? 1 : -1;
                        } else {
                            return (aValue < bValue) ? 1 : -1;
                        }
                    });

                    const totalRecords = filteredRecords.length;

                    const paginatedRecords = filteredRecords.slice(start, start + length);

                    await Promise.all(paginatedRecords.map(async (childData) => {
                        if (childData.id) {
                            const totalOrderSnapShot = await database.collection('orders').where('userId', '==', childData.id).get();
                            const rides = totalOrderSnapShot.size;

                            const totalIntercityOrderSnapShot = await database.collection('orders_intercity').where('userId', '==', childData.id).get();
                            const intercity = totalIntercityOrderSnapShot.size;

                            childData.total_rides = rides + intercity;
                        } else {
                            childData.total_rides = 0;
                        }
                    }));

                    paginatedRecords.forEach(function (childData) {
                        var id = childData.id;
                        var route1 = '{{route("users.edit", ":id")}}';
                        route1 = route1.replace(':id', id);

                        var userview = '{{route("users.view", ":id")}}';
                        userview = userview.replace(':id', id);
                        var date = '';
                        var time = '';
                        if (childData.hasOwnProperty("createdAt")) {
                            try {
                                date = childData.createdAt.toDate().toDateString();
                                time = childData.createdAt.toDate().toLocaleTimeString('en-US');
                            } catch (err) {
                            }
                        }

                        if(childData.countryCode.includes('+')){
                            childData.countryCode = childData.countryCode.slice(1);
                        } 
                        else
                        {
                            childData.countryCode = childData.countryCode;
                        }


                        records.push([
                            checkDeletePermission ? '<td class="delete-all"><input type="checkbox" id="is_open_' + id + '" class="is_open" dataId="' + id + '"><label class="col-3 control-label" for="is_open_' + id + '" ></label></td>' : '',
                            childData.profilePic == '' || childData.profilePic == null ? '<img class="rounded" style="width:50px" src="' + defaultImg + '" alt="image">' : '<img class="rounded" style="width:50px" src="' + childData.profilePic + '" alt="image">',
                            '<a href="' + userview + '">' + childData.fullName + '</a>',
                            shortEmail(childData.email),
                            '+' + shortNumber(childData.countryCode,childData.phoneNumber),
                            date + ' ' + time,
                            childData.isActive ? '<label class="switch"><input type="checkbox" checked id="' + childData.id + '" name="isActive"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" id="' + childData.id + '" name="isActive"><span class="slider round"></span></label>',
                            '<td class="total_rides_' + childData.id + '">' + childData.total_rides + '</td>',
                            '<span class="action-btn"><a href="' + userview + '"><i class="fa fa-eye"></i></a><a href="' + route1 + '"><i class="fa fa-edit"></i></a><?php if (in_array('user.delete', json_decode(@session('user_permissions')))) { ?> <a id="' + childData.id + '" class="delete-btn" name="user-delete" href="javascript:void(0)"><i class="fa fa-trash"></i></a><?php } ?></span>'

                        ]);
                    });

                    console.log("Records fetched:", records.length);

                    $('#overlay').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: totalRecords, // Total number of records in Firestore
                        recordsFiltered: totalRecords, // Number of records after filtering (if any)
                        data: records // The actual data to display in the table
                    });
                }).catch(function (error) {
                    console.error("Error fetching data from Firestore:", error);
                    $('#overlay').hide(); // Hide loader
                    callback({
                        draw: data.draw,
                        recordsTotal: 0,
                        recordsFiltered: 0,
                        data: [] // No data due to error
                    });
                });
            },
            order: [[5, 'desc'], [2, 'asc'], [3, 'asc'], [4, 'asc']],
            columnDefs: [
                {
                    targets: (checkDeletePermission) ? 5 : 4,
                    type: 'date',
                    render: function (data) {
                        return data;
                    }
                },
                { orderable: false, targets: (checkDeletePermission) ? [0, 1, 6, 7, 8] : [0, 5, 6, 7] },
            ],
            "language": {
                "zeroRecords": "{{trans("lang.no_record_found")}}",
                "emptyTable": "{{trans("lang.no_record_found")}}",
                "processing": "" // Remove default loader
            },

        });

        function debounce(func, wait) {
            let timeout;
            const context = this;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        $('#search-input').on('input', debounce(function () {
            const searchValue = $(this).val();
            if (searchValue.length >= 3) {
                $('#overlay').show();
                table.search(searchValue).draw();
            } else if (searchValue.length === 0) {
                $('#overlay').show();
                table.search('').draw();
            }
        }, 300));
    });

    $("#is_active").click(function () {
        $("#userTable .is_open").prop('checked', $(this).prop('checked'));
    });

    $("#deleteAll").click(function () {
        if ($('#userTable .is_open:checked').length) {
            if (confirm(deleteSelectedRecordMsg)) {
                jQuery("#overlay").show();
                $('#userTable .is_open:checked').each(function () {
                    var dataId = $(this).attr('dataId');

                    database.collection('users').doc(dataId).delete().then(function () {
                        deleteUserData(dataId);
                        setTimeout(function () {
                            window.location.reload();
                        }, 7000);
                    });
                });
            } else {
                return false;
            }
        } else {
            alert("{{trans('lang.select_delete_alert')}}");
        }
    });

    async function deleteUserData(userId) {
        //delete user from authentication
        var dataObject = { "data": { "uid": userId } };
        var projectId = '<?php echo env('FIREBASE_PROJECT_ID') ?>';
        jQuery.ajax({
            url: 'https://us-central1-' + projectId + '.cloudfunctions.net/deleteUser',
            method: 'POST',
            crossDomain: true,
            contentType: "application/json; charset=utf-8",
            data: JSON.stringify(dataObject),
            success: function (data) {
                console.log('Delete user success:', data.result);
            },
            error: function (xhr, status, error) {
                var responseText = JSON.parse(xhr.responseText);
                console.log('Delete user error:', responseText.error);
            }
        });
    }

    $(document).on("click", "a[name='user-delete']", function (e) {

        if (confirm(deleteMsg)) {
            jQuery("#overlay").show();
            var id = this.id;

            database.collection('users').doc(id).delete().then(function (result) {
                deleteUserData(id);
                setTimeout(function () {
                    window.location.href = '{{ url()->current() }}';
                }, 7000);
            });

        } else {
            return false;
        }

    });

    $(document).on("click", "input[name='isActive']", function (e) {
        var ischeck = $(this).is(':checked');
        var id = this.id;
        database.collection('users').doc(id).update({ 'isActive': ischeck ? true : false }).then(function (result) {
        });
    });
</script>

@endsection