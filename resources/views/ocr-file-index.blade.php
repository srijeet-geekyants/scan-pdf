@php use Illuminate\Contracts\Session\Session; @endphp
@extends('layouts.app')

@section('content')
    <div class="row col-md-12">
        <h3 class="text-center">Scan PDF's</h3>
    </div>
    <div class="row col-md-12">
        <div class="col-md-4 mx-auto">
            <form action="{{route('ocr-files.upload')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col"><input type="file" name="file" class="form-control" accept="application/pdf"/>
                    </div>
                    <div class="col-1">
                        <button type="submit" class="btn btn-success btn-sm">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row my-3 col-md-12">
        <div class="col-md-4 mx-auto">
            @if(\Session::has('message'))
                <div class="alert {{\Session::get('alert-class', 'alert-success')}} row">
                    <strong class="col">{{\Session::get('message')}}</strong>
                    <button type="button" class="close col-1" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="row my-3 col-md-12">
        <div class="col-md-10 mx-auto">
            <table class="table table-striped table-bordered table-hover filesTable">
                <thead>
                <tr class="text-center">
                    <th>Sr No</th>
                    <th>Name</th>
                    <td>Upload Date</td>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @if(count($ocrFiles))
                    @foreach($ocrFiles as $ocrFile)
                        <tr>
                            <td>{{$ocrFile->id}}</td>
                            <td class="text-center">{{$ocrFile->name}}</td>
                            <td class="text-center">
                                {{Carbon\Carbon::parse($ocrFile->created_at)->format('d/m/y H:i')}}
                            </td>
                            <td class="text-center">
                                @if($ocrFile->status === 'pending_drawing')
                                    <span class="text-danger">{{$ocrFile->status}}</span>
                                @elseif($ocrFile->status === 'processing')
                                    <span class="text-warning">{{$ocrFile->status}}</span>
                                @elseif($ocrFile->status === 'processed')
                                    <span class="text-primary">{{$ocrFile->status}}</span>
                                @elseif($ocrFile->status === 'error')
                                    <span class="text-danger">{{$ocrFile->status}}</span>
                                @elseif($ocrFile->status === 'completed')
                                    <span class="text-success">{{$ocrFile->status}}</span>
                                @endif
                            </td>
                            <td>
                                @if($ocrFile->status === "pending_drawing")
                                    <button class="btn btn-primary btn-sm open-ocr-popup-button" type="button"
                                            data-bs-toggle="modal" data-bs-target="#fileViewModal"
                                            data-url={{route('ocr-files.openOcrPopup', ['ocrFileId' => $ocrFile->id, 'type' => "pending_drawing"])}}
                                    >
                                        Draw
                                    </button>
                                @elseif($ocrFile->status === "processing")
                                    <span class="text-warning">Processing</span>
                                @elseif($ocrFile->status === "processed")
                                    <span class="text-success">Processed</span>
                                @elseif($ocrFile->status === "error")
                                    <span class="text-danger">Error</span>
                                @else
                                    <button class="btn btn-primary btn-sm" type="button">View files
                                        <button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center">
                            No Records Found
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="fileViewModal" tabindex="-1" role="dialog" aria-labelledby="fileViewModal"
         aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header row">
                    <h5 class="modal-title col" id="exampleModalLongTitle">Pending Drawing Area</h5>
                    <button type="button" class="close col-1 ml-left" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true text-danger">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pendingCoordinatesForm">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn-sm btn btn-primary">Finsh</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('customjs')
    <script>
        $(function () {
            $('.fileTable').DataTable({});

            $('.open-ocr-popup-button').click(function () {
                $('#pendingCoordinatesForm').load($(this).data('url'));
                // $().load($(this).data('url'));
            });
        });
    </script>
@endsection
