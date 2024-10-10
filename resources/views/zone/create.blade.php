@extends('layouts.app')

@section('content')
<div class="page-wrapper">

    <div class="row page-titles">

        <div class="col-md-5 align-self-center">
            <h3 class="text-themecolor">{{trans('lang.zone_plural')}}</h3>
        </div>

        <div class="col-md-7 align-self-center">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/dashboard')}}">{{trans('lang.dashboard')}}</a></li>
                <li class="breadcrumb-item"><a href="{!! route('zone') !!}">{{trans('lang.zone_plural')}}</a></li>
                <li class="breadcrumb-item active">{{trans('lang.zone_create')}}</li>
            </ol>
        </div>
    </div>
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="error_top" style="display:none"></div>

                <div class="row restaurant_payout_create">
                    <div class="restaurant_payout_create-inner">

                        <fieldset>

                            <legend>{{trans('lang.zone_create')}}</legend>

                            <div class="form-group row width-100">
                                <label class="col-3 control-label">{{trans('lang.zone_name')}}<span
                                            class="required-field"></span></label>
                                <div class="col-7">
                                    <input type="text" class="form-control" id="name">
                                    <div class="form-text text-muted">{{ trans("lang.zone_name_help") }}</div>
                                </div>
                            </div>

                            <div class="form-group row width-100">
                                <div class="form-check">
                                    <input type="checkbox" class="publish" id="publish">
                                    <label class="col-3 control-label" for="publish">{{trans('lang.status')}}</label>
                                </div>
                            </div>

                            <div class="form-hidden">
                                <input type="hidden" id="coordinates" name="coordinates" value="">
                            </div>

                        </fieldset>

                    </div>

                </div>

                <div class="row mt-5">
                    <div class="col-sm-5">
                        <div class="row">
                            <div class="col-sm-12">
                                <h4>{{trans('lang.instructions')}}</h4>
                                <p>{{trans('lang.instructions_help')}}</p>
                                <p><i class="fa fa-hand-pointer-o map_icons"></i>{{trans('lang.instructions_hand_tool')}}</p>
                                <p><i class="fa fa-plus-circle map_icons"></i>{{trans('lang.instructions_shape_tool')}}</p>
                                <p><i class="fa fa-trash map_icons"></i>{{trans('lang.instructions_trash_tool')}}</p>
                            </div>
                            <div class="col-sm-12">
                                <img src="{{asset('images/zone_info.gif')}}" alt="GIF" width="100%">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <input type="text" placeholder="{{ trans('lang.search_location') }}" id="search-box"
                            class="form-control controls" />
                        <div id="map"></div>
                    </div>

                    <div class="col-sm-2">
                        <ul style="list-style: none;padding:0">
                            <li>
                                <a id="select-button" href="javascript:void(0)"
                                    onclick="drawingManager.setDrawingMode(null)"
                                    class="btn-floating zone-add-btn btn-large waves-effect waves-light tooltipped"
                                    title="Use this tool to drag the map and select your desired location">
                                    <i class="fa fa-hand-pointer-o map_icons"></i>
                                </a>
                            </li>
                            <li>
                                <a id="add-button" href="javascript:void(0)"
                                    onclick="drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON)"
                                    class="btn-floating zone-add-btn btn-large waves-effect waves-light tooltipped"
                                    title="Use this tool to highlight areas and connect the dots">
                                    <i class="fa fa-plus-circle map_icons"></i>
                                </a>
                            </li>
                            <li>
                                <a id="delete-all-button" href="javascript:void(0)" onclick="clearMap()"
                                    class="btn-floating zone-delete-all-btn btn-large waves-effect waves-light tooltipped"
                                    title="Use this tool to delete all selected areas">
                                    <i class="fa fa-trash map_icons"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
                
                <div class="form-group col-12 text-center btm-btn">

                    <button type="button" class="btn btn-primary save-form-btn">
                        <i class="fa fa-save"></i> {{trans('lang.save')}}
                    </button>

                    <a href="{!! route('zone') !!}" class="btn btn-default">
                        <i class="fa fa-undo"></i>{{trans('lang.cancel')}}
                    </a>

                </div>

            </div>
        </div>
    </div>
</div>

@endsection

<style>
    #map {
        height: 500px;
        width: 100%;
    }

    #panel {
        width: 200px;
        font-family: Arial, sans-serif;
        font-size: 13px;
        float: right;
        margin: 10px;
        margin-top: 100px;
    }

    #delete-button,
    #add-button,
    #delete-all-button,
    #save-button {
        margin-top: 5px;
    }

    #search-box {
        background-color: #f7f7f7;
        font-size: 15px;
        font-weight: 300;
        margin-top: 10px;
        margin-bottom: 10px;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        height: 25px;
        border: 1px solid #c7c7c7;
    }

    .map_icons {
        font-size: 24px;
        color: white;
        padding: 10px;
        background-color: #000;
        margin: 5px;
    }
</style>

@section('scripts')

<script>

    var database = firebase.firestore();
    var id = database.collection("tmp").doc().id;
    var ref = database.collection('zone');

    $(document).ready(function () {

        setTimeout(function(){
            initMap();
        },2500);
        
        $(".save-form-btn").click(function () {
            
            var name = $("#name").val();
            var publish = $("#publish").is(":checked");
            var coordinates_object = $('#coordinates').val();
            
            $(".error_top").empty();
            if (name == '') {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.zone_name_error')}}</p>");
                window.scrollTo(0, 0);
            } else if (coordinates_object == "") {
                $(".error_top").show();
                $(".error_top").html("");
                $(".error_top").append("<p>{{trans('lang.zone_coordinates_error')}}</p>");
                window.scrollTo(0, 0);
            } else {

                var coordinates_parse = $.parseJSON(coordinates_object);
                var coordinates = coordinates_parse[0];
                var latitude = coordinates[0]['lat'];
                var longitude = coordinates[0]['lng'];

                var area = [];
                for (let i = 0; i < coordinates.length; i++) {
                    var item = coordinates[i];
                    area.push(new firebase.firestore.GeoPoint(item.lat,item.lng));
                }
                
                jQuery("#overlay").show();
                database.collection('zone').doc(id).set({
                    'id': id,
                    'name': name,
                    'latitude': latitude,
                    'longitude': longitude,
                    'area': area,
                    'publish': publish,
                }).then(function (result) {
                    jQuery("#overlay").hide();
                    window.location.href = '{{ route("zone")}}';
                });
            }
        });
    });

    var map;
    var drawingManager;
    var selectedShape;
    var selectedKernel;
    var gmarkers = [];
    var coordinates = [];
    var allShapes = [];
    var sendable_coordinates = [];
    var shapeColor = "#007cff";
    var kernelColor = "#000";
    var default_lat = getCookie('default_latitude');
    var default_lng = getCookie('default_longitude');

    function setMapOnAll(map) {
        for (var i = 0; i < gmarkers.length; i++) {
            gmarkers[i].setMap(map);
        }
    }

    function clearMarkers() {
        setMapOnAll(null);
    }

    function deleteMarkers() {
        clearMarkers();
        gmarkers = [];
    }

    function deleteSelectedShape() {
        if (selectedShape) {
            selectedShape.setMap(null);
            var index = allShapes.indexOf(selectedShape);
            if (index > -1) {
                allShapes.splice(index, 1);
            }
        }
        if (selectedKernel) {
            selectedKernel.setMap(null);
        }

        let lat_lng = [];
        allShapes.forEach(function(data, index) {
            lat_lng[index] = getCoordinates(data);
        });

        if(lat_lng.length == 0){
            document.getElementById('coordinates').value = '';
        }else{
            document.getElementById('coordinates').value = JSON.stringify(lat_lng);
        }
    }

    function clearMap() {
        if (allShapes.length > 0) {
            for (var i = 0; i < allShapes.length; i++){
                allShapes[i].setMap(null);
            }
            allShapes = [];
            deleteMarkers();
            document.getElementById('coordinates').value = null;
        }
    }

    function clearSelection() {
        if (selectedShape) {
            if (selectedShape.type !== 'marker') {
                selectedShape.setEditable(false);
            }
            selectedShape = null;
        }

        if (selectedKernel) {
            if (selectedKernel.type !== 'marker') {
                selectedKernel.setEditable(false);
            }
            selectedKernel = null;
        }
    }

    function setSelection(shape, check) {
        clearSelection();
        shape.setEditable(true);
        shape.setDraggable(true);
        if (check) {
            selectedKernel = shape;
        } else {
            selectedShape = shape;
        }
    }

    function getCoordinates(polygon) {
        var path = polygon.getPath();
        coordinates = [];
        for (var i = 0; i < path.length; i++) {
            coordinates.push({
                lat: path.getAt(i).lat(),
                lng: path.getAt(i).lng()
            });
        }
        return coordinates;
    }

    function createMarker(coord, nr, map) {
        var mesaj = "<h6>Vârf " + nr + "</h6><br>" + "Lat: " + coord.lat + "<br>" + "Lng: " + coord.lng;
        var marker = new google.maps.Marker({
            position: coord,
            map: map,
        });
        
        google.maps.event.addListener(marker, 'click', function() {
            infowindow.setContent(mesaj);
            infowindow.open(map, marker);
        });
        google.maps.event.addListener(marker, 'dblclick', function() {
            marker.setMap(null);
        });
        return marker;
    }

    function searchBox() {
        var input = document.getElementById('search-box');
        var searchBox = new google.maps.places.SearchBox(input);
        map.addListener('bounds_changed', function() {
            searchBox.setBounds(map.getBounds());
        });

        searchBox.addListener('places_changed', function() {
            var places = searchBox.getPlaces();
            if (places.length == 0) {
                return;
            }
            var bounds = new google.maps.LatLngBounds();
            places.forEach(function(place) {
                if (!place.geometry) {
                    return;
                }
                var icon = {
                    url: place.icon,
                    size: new google.maps.Size(71, 71),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point(17, 34),
                    scaledSize: new google.maps.Size(25, 25)
                };
                if (place.geometry.viewport) {
                    bounds.union(place.geometry.viewport);
                } else {
                    bounds.extend(place.geometry.location);
                }
            });
            map.fitBounds(bounds);
        });

    }

    function initMap() {

        var infowindow = new google.maps.InfoWindow({
            size: new google.maps.Size(150, 50)
        });
        
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 8,
            center: new google.maps.LatLng(default_lat, default_lng),
            mapTypeControl: false,
            mapTypeControlOptions: {
                style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                position: google.maps.ControlPosition.LEFT_CENTER
            },
            zoomControl: true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            scaleControl: false,
            scaleControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER
            },
            streetViewControl: false,
            fullscreenControl: false
        });

        searchBox();

        var shapeOptions = {
            strokeWeight: 1,
            fillOpacity: 0.4,
            editable: true,
            draggable: true
        };

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: null,
            drawingControl: false,
            drawingControlOptions: {
                position: google.maps.ControlPosition.RIGHT_CENTER,
                drawingModes: ['polygon']
            },
            polygonOptions: shapeOptions,
            map: map
        });

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {

            var newShape = e.overlay;
            allShapes.push(newShape);
            let lat_lng = [];
            allShapes.forEach(function(data, index) {
                lat_lng[index] = getCoordinates(data);
            });
            document.getElementById('coordinates').value = JSON.stringify(lat_lng);

            newShape.setOptions({
                fillColor: shapeColor
            });

            getCoordinates(newShape);
            drawingManager.setDrawingMode(null);
            setSelection(newShape, 0);

            google.maps.event.addListener(newShape, 'click', function(e) {
                if (e.vertex !== undefined) {
                    var path = newShape.getPaths().getAt(e.path);
                    path.removeAt(e.vertex);
                    getCoordinates(newShape);
                    if (path.length < 3) {
                        newShape.setMap(null);
                    }
                }
                setSelection(newShape, 0);
            });

            //update coordinates
            google.maps.event.addListener(newShape, 'click', function(e) {
                getCoordinates(newShape);
            });
            google.maps.event.addListener(newShape, "dragend", function(e) {
                getCoordinates(newShape);
            });
            google.maps.event.addListener(newShape.getPath(), "insert_at", function(e) {
                getCoordinates(newShape);
            });
            google.maps.event.addListener(newShape.getPath(), "remove_at", function(e) {
                getCoordinates(newShape);
            });
            google.maps.event.addListener(newShape.getPath(), "set_at", function(e) {
                getCoordinates(newShape);
            });
        });

        google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
        google.maps.event.addListener(map, 'click', clearSelection);
    }

</script>

@endsection
