@extends('layouts.app')

@section('content')

<div class="page-wrapper">

    <div class="row page-titles">
        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.users_wallet_transactions')}}</h3>
        </div>
        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.users_wallet_transactions')}}</li>
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


                                        <th>{{trans('lang.id')}}</th>
                                        <th>{{trans('lang.user_name')}}</th>
                                        <th>{{trans('lang.payment_method')}}</th>
                                        <th>{{trans('lang.total_amount')}}</th>
                                        <th>{{trans('lang.note')}}</th>
                                        <th>{{trans('lang.date')}}</th>
                                        <th>{{trans('lang.actions')}} </th>

                                    </tr>
                                </thead>
                                <tbody id="append_list1">
                                </tbody>
                            </table>

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

    var symbolAtRight = false;
    var ref = database.collection('wallet_transaction').where("userType", "==", 'customer').orderBy('createdDate', 'desc');
    var refCurrency = database.collection('currency').where('enable', '==', true).limit('1');
    refCurrency.get().then(async function (snapshots) {
        var currencyData = snapshots.docs[0].data();
        currentCurrency = currencyData.symbol;
        decimal_degits = currencyData.decimalDigits;

        if (currencyData.symbolAtRight) {
            symbolAtRight = true;
        }
    });

    var append_list = '';

    var deleteMsg = "{{trans('lang.delete_alert')}}";
    var deleteSelectedRecordMsg = "{{trans('lang.selected_delete_alert')}}";

    $(document).ready(function () {
        $(document.body).on('click', '.redirecttopage', function () {
            var url = $(this).attr('data-url');
            window.location.href = url;
        });
        jQuery("#overlay").show();

        const table = $('#userTable').DataTable({
            pageLength: 10, // Number of rows per page
            processing: false, // Show processing indicator
            serverSide: true, // Enable server-side processing
            responsive: true,
            ajax: async function (data, callback, settings) {
                const start = data.start;
                const length = data.length;
                const searchValue = data.search.value.toLowerCase();
                const orderColumnIndex = data.order[0].column;
                const orderDirection = data.order[0].dir;
                const orderableColumns = ['id', 'userName', '', 'amount', 'note', 'createdDate', '']; // Ensure this matches the actual column names
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
                    let userNames = {};


                    // Fetch driver names
                    const userDocs = await database.collection('users').get();
                    userDocs.forEach(doc => {
                        userNames[doc.id] = doc.data().fullName;
                    });

                    await Promise.all(querySnapshot.docs.map(async (doc) => {

                        childData = doc.data();

                        childData.id = doc.id; // Ensure the document ID is included in the data              
                        childData.userName = userNames[childData.userId] || '';

                        if (searchValue) {
                            var date = '';
                            var time = '';
                            if (childData.hasOwnProperty("createdDate")) {
                                try {
                                    date = childData.createdDate.toDate().toDateString();
                                    time = childData.createdDate.toDate().toLocaleTimeString('en-US');
                                } catch (err) {
                                }
                            }
                            var createdAt = date + ' ' + time;

                            if (
                                (childData.id && childData.id.toLowerCase().toString().includes(searchValue)) ||
                                (childData.userName && childData.userName.toLowerCase().toString().includes(searchValue)) ||
                                (createdAt && createdAt.toString().toLowerCase().indexOf(searchValue) > -1) ||
                                (childData.amount && childData.amount.toString().includes(searchValue)) ||
                                (childData.note && childData.note.toString().toLowerCase().includes(searchValue))

                            ) {

                                filteredRecords.push(childData);
                            }
                        } else {
                            filteredRecords.push(childData);
                        }
                    }));
                    filteredRecords.sort((a, b) => {
                        let aValue = a[orderByField] ? a[orderByField].toString().toLowerCase() : '';
                        let bValue = b[orderByField] ? b[orderByField].toString().toLowerCase() : '';
                        if (orderByField === 'createdDate') {
                            aValue = a[orderByField] ? new Date(a[orderByField].toDate()).getTime() : 0;
                            bValue = b[orderByField] ? new Date(b[orderByField].toDate()).getTime() : 0;
                        }
                        if (orderByField === 'amount') {
                            aValue = a[orderByField] ? parseFloat(a[orderByField]) : 0;
                            bValue = b[orderByField] ? parseFloat(b[orderByField]) : 0;
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
                        childData.paymentIMG = await getPaymentImage(childData.paymentType, childData.id);
                        var getData = await buildHTML(childData);
                        records.push(getData);
                    }));

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
            order: [[5, 'desc']],
            columnDefs: [
                {
                    targets: 5,
                    type: 'date',
                    render: function (data) {
                        return data;
                    }
                },
                { orderable: false, targets: [2, 6] },
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


    async function buildHTML(val) {
        var html = [];
        newdate = '';
        var id = val.userId;
        var route1 = '{{route("users.view", ":id")}}';
        route1 = route1.replace(':id', id);
        var trroute1 = '';
        trroute1 = trroute1.replace(':id', id);
        var t_id = val.id;
        var transaction = t_id.substring(0, 7);
        html.push(transaction);
        if (val.userId) {
            if (val.userName != '') {
                html.push('<a class="user_role_' + val.userId + '" href="' + route1 + '">' + val.userName + '</a>');

            } else {
                html.push('{{trans("lang.unknown_user")}}');
            }
        } else {
            html.push('');
        }
        html.push('<span class="payment_method_' + val.id + '"><img style="width:100px" src="' + val.paymentIMG + '" alt="image"></span>');
        if (val.amount.charAt(0) == "-") {
            amount = Math.abs(val.amount);
            if (symbolAtRight) {
                amount = parseFloat(amount).toFixed(decimal_degits) + currentCurrency;
            } else {
                amount = currentCurrency + parseFloat(amount).toFixed(decimal_degits);
            }
            html.push('<span class="text-danger">(-' + amount + ')</span>');
        } else {
            if (symbolAtRight) {
                amount = parseFloat(val.amount).toFixed(decimal_degits) + currentCurrency;
            } else {
                amount = currentCurrency + parseFloat(val.amount).toFixed(decimal_degits);
            }
            html.push('<span class="text-success">(' + amount + ')</span>');
        }
        html.push(val.note);
        if (val.hasOwnProperty("createdDate")) {
            var date = val.createdDate.toDate().toDateString();
            var time = val.createdDate.toDate().toLocaleTimeString('en-US');
            html.push('<span class="dt-time">' + date + ' ' + time + '</span>');
        } else {
            html.push('');
        }
        if (val.orderType != null && val.orderType != '') {
            if (val.orderType == 'city') {
                var trroute1 = '{{route("rides.show", ":id")}}';
                trroute1 = trroute1.replace(':id', val.transactionId);
            } else {
                var trroute1 = '{{route("intercity-service-rides.view", ":id")}}';
                trroute1 = trroute1.replace(':id', val.transactionId);
            }
            html.push('<span class="action-btn"><a href="' + trroute1 + '"><i class="fa fa-file-pdf-o" aria-hidden="true"></i></a></span>');
        } else {
            html.push('');
        }
        return html;
    }

    async function getPaymentImage(paymentType, id) {
        var img = '';
        paymentType = paymentType.charAt(0).toUpperCase() + paymentType.slice(1);

        await database.collection('settings').doc('payment').get().then(async function (snapshots) {
            var payment = snapshots.data();
            var payamentData = Object.values(payment).filter((data) => data.name == paymentType).map((filterData) => filterData)
            img = payamentData[0].image;
        });
        return img;
    }

</script>

@endsection