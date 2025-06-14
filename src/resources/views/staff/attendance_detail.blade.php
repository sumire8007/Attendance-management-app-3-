@extends('layouts.staff_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')

    @if ($attendanceId !== null && $applicationId == null && $waitApproval == null)<!-- 一度も申請したことない場合　-->
        <div class="attendance_group">
            <div class="attendance_title">
                <h2>勤怠詳細</h2>
            </div>
            <form action="/attendance/application" method="post">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $attendanceDates->id }}">
                <div class="attendance_table">
                    <table>
                        <tr>
                            <th>名前</th>
                            <td>{{ $attendanceDates->user->name }}</td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td>{{ $date->year . '年' . $date->month . '月' . $date->day . '日' }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                @error("clock_in_change_at")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <input type="time"  name="clock_in_change_at" value="{{ old('clock_in_change_at',$in) }}">
                                <p>~</p>
                                <input type="time" name="clock_out_change_at" value="{{ old('clock_out_change_at',$out) }}">
                            </td>
                        </tr>
                        @foreach($restDates as $index => $restDate)
                            <input type="hidden" name="rest_id[]" value="{{ $restDate->rest->id  }}">
                            <tr>
                                <th>休憩{{ $index + 1 }}</th>
                                <td>
                                    @error("rest_in_at.$index")
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror

                                    <input type="time" name="rest_in_at[]" value="{{ old('rest_in_at.'.$index,\Carbon\Carbon::parse($restDate->rest->rest_in_at)->format('H:i')) }}">
                                    <p>~</p>
                                    <input type="time" name="rest_out_at[]" value="{{ old('rest_out_at.'.$index,$restDate->rest->rest_out_at ? \Carbon\Carbon::parse($restDate->rest->rest_out_at)->format('H:i') : '') }}">
                                </td>
                            </tr>
                        @endforeach
                        @php
                            $restDateIndex = count($restDates)
                        @endphp
                        <tr>
                            <th>休憩 {{ $restDateIndex + 1 }}</th>
                            <td>
                                @error("rest_in_at.$restDateIndex")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <input type="time" name="rest_in_at[]" value="{{ old('rest_in_at.'.$restDateIndex) }}">
                                <p>~</p>
                                <input type="time" name="rest_out_at[]" value="{{ old('rest_out_at.'.$restDateIndex) }}">
                                <input type="hidden" name="rest_id[]" value="">
                            </td>
                        </tr>
                        <tr>
                            <th>備考</th>
                            <td class="textarea">
                                @error("remark_change")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <textarea name="remark_change"  cols="20" rows="3">{{ old('remark_change',$attendanceDates->remark) }}</textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="attendance_correction_button">
                    <button>修正</button>
                </div>
            </form>
        </div>
    @elseif(!empty($approval))<!-- 　1回以上申請したことがあり、再申請用　-->
        <div class="attendance_group">
            <div class="attendance_title">
                <h2>勤怠詳細</h2>
            </div>
            <form action="/attendance/application" method="post">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $attendanceDates->id }}">
                <div class="attendance_table">
                    <table>
                        <tr>
                            <th>名前</th>
                            <td>{{ $attendanceApplicationDate->user->name }}</td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td>{{ \Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->attendance_change_date)->format('Y' . '年' . 'm' . '月' . 'd' . '日') }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                @error("clock_in_change_at")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <input type="time" name="clock_in_change_at" value="{{ old('clock_in_change_at',\Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->clock_in_change_at)->format('H:i')) }}">
                                <p>~</p>
                                <input type="time" name="clock_out_change_at" value="{{ old('clock_out_change_at',\Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->clock_out_change_at)->format('H:i')) }}">
                            </td>
                        </tr>
                            @foreach($restApplicationDates as $index => $restApplication)
                                @php
                                    $restApp = $restApplication->restApplication;
                                @endphp
                                @if($restApp)
                                    <input type="hidden" name="rest_id[]" value="{{ $restApp->rest_id  }}">
                                    <tr>
                                        <th>休憩{{ $index + 1 }}</th>
                                        <td>
                                            @error("rest_in_at.$index")
                                                <span class="error-message">{{ $message }}</span>
                                            @enderror

                                            <input type="time" name="rest_in_at[]"
                                                value="{{ old('rest_in_at.'.$index,\Carbon\Carbon::parse($restApp->rest_in_change_at)->format('H:i')) }}">
                                            <p>~</p>
                                            <input type="time" name="rest_out_at[]"
                                                value="{{ old('rest_out_at.'.$index,\Carbon\Carbon::parse($restApp->rest_out_change_at)->format('H:i')) }}">
                                        </td>
                                    </tr>
                                @endif
                            @endforeach

                        @php
                            $restDateIndex = count($restApplicationDates) + 1
                        @endphp
                        <tr>
                            <th>休憩 {{ $restDateIndex + 1 }}</th>
                            <td>
                                @error("rest_in_at.$restDateIndex")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <input type="time" name="rest_in_at[]" value="{{ old('rest_in_at.'.$restDateIndex) }}">
                                <p>~</p>
                                <input type="time" name="rest_out_at[]" value="{{ old('rest_out_at.'.$restDateIndex) }}">
                                <input type="hidden" name="rest_id[]" value="">
                            </td>
                        </tr>
                        <tr>
                            <th>備考</th>
                            <td class="textarea">
                                @error("remark_change")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <textarea name="remark_change" cols="20" rows="3">{{ old('remark_change',$attendanceApplicationDate->attendanceApplication->remark_change) }}</textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="attendance_correction_button">
                    <button>修正</button>
                </div>
            </form>
        </div>
    @elseif(isset($waitApproval))<!-- 承認待ち　-->
        <div class="attendance_group">
            <div class="attendance_title">
                <h2>勤怠詳細</h2>
            </div>
                <div class="attendance_table">
                    <table>
                        <tr>
                            <th>名前</th>
                            <td>
                                <p>{{ $attendanceApplicationDate->user->name }}</p>
                            </td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td>
                            <p>{{ \Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->attendance_change_date)->format('Y' . '年' . 'm' . '月' . 'd' . '日') }}</p>
                            </td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                <p>{{ \Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->clock_in_change_at)->format('H:i') }}</p>
                                <p>~</p>
                                <p>{{ \Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->clock_out_change_at)->format('H:i') }}</p>
                            </td>
                        </tr>
                        @foreach($restApplicationDates as $index => $restApplication)
                            @php
                                $restApp = $restApplication->restApplication;
                            @endphp
                            @if($restApp)
                                <tr>
                                    <th>休憩{{ $index + 1 }}</th>
                                    <td>
                                        <p>{{ \Carbon\Carbon::parse($restApp->rest_in_change_at)->format('H:i') }}</p>
                                        <p>~</p>
                                        <p>{{ \Carbon\Carbon::parse($restApp->rest_out_change_at)->format('H:i') }}</p>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        @php
                            $restDateIndex = count($restApplicationDates) + 1
                        @endphp
                        <tr>
                            <th>休憩{{ $restDateIndex }}</th>
                            <td>
                                <p></p>
                            </td>
                        </tr>
                        <tr>
                            <th>備考</th>
                            <td class="textarea">
                                <p>{{ $attendanceApplicationDate->attendanceApplication->remark_change }}</p>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="waiting-approval-message">
                    <p>*承認待ちのため修正はできません。</p>
                </div>
        </div>
    @endif


@endsection