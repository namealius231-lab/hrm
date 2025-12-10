<?php

namespace App\Http\Controllers;

use App\Models\AccountList;
use App\Models\Announcement;
use App\Models\AttendanceEmployee;
use App\Models\Employee;
use App\Models\Event;
use App\Models\LandingPageSection;
use App\Models\Meeting;
use App\Models\Job;
use App\Models\Payees;
use App\Models\Payer;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            if(Auth::check())
            {
                $user = Auth::user();
                
                if($user->type == 'employee')
                {
                    try {
                        $emp = Employee::where('user_id', '=', $user->id)->first();
                        
                        if (!$emp) {
                            \Log::warning('Employee not found for user: ' . $user->id);
                            return redirect('login')->with('error', __('Employee profile not found.'));
                        }

                        $announcements = [];
                        $meetings = [];
                        $arrEvents = [];
                        $employees = collect([]);
                        $employeeAttendance = null;
                        $officeTime = ['startTime' => '09:00', 'endTime' => '18:00'];

                        try {
                            $announcements = Announcement::orderBy('announcements.id', 'desc')
                                ->take(5)
                                ->leftjoin('announcement_employees', 'announcements.id', '=', 'announcement_employees.announcement_id')
                                ->where('announcement_employees.employee_id', '=', $emp->id)
                                ->orWhere(function ($q){
                                    $q->where('announcements.department_id', '["0"]')
                                      ->where('announcements.employee_id', '["0"]');
                                })
                                ->get();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching announcements: ' . $e->getMessage());
                        }

                        try {
                            $employees = Employee::get();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching employees: ' . $e->getMessage());
                        }

                        try {
                            $meetings = Meeting::orderBy('meetings.id', 'desc')
                                ->take(5)
                                ->leftjoin('meeting_employees', 'meetings.id', '=', 'meeting_employees.meeting_id')
                                ->where('meeting_employees.employee_id', '=', $emp->id)
                                ->orWhere(function ($q){
                                    $q->where('meetings.department_id', '["0"]')
                                      ->where('meetings.employee_id', '["0"]'); 
                                })
                                ->get();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching meetings: ' . $e->getMessage());
                        }

                        try {
                            $events = Event::select('events.*','events.id as event_id_pk','event_employees.*')
                                ->leftjoin('event_employees', 'events.id', '=', 'event_employees.event_id')
                                ->where('event_employees.employee_id', '=', $emp->id)
                                ->orWhere(function ($q){
                                    $q->where('events.department_id', '["0"]')
                                      ->where('events.employee_id', '["0"]');
                                })
                                ->get();
                            
                            foreach($events as $event)
                            {
                                $arr['id']        = $event['id'] ?? null;
                                $arr['title']     = $event['title'] ?? '';
                                $arr['start']     = $event['start_date'] ?? date('Y-m-d');
                                $arr['end']       = $event['end_date'] ?? date('Y-m-d');
                                $arr['className'] = $event['color'] ?? 'event-primary';
                                $arr['url']       = route('eventsshow', (!empty($event['event_id_pk'])) ? $event['event_id_pk'] : '' );
                                $arrEvents[] = $arr;
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error fetching events: ' . $e->getMessage());
                        }

                        try {
                            $date = date("Y-m-d");
                            $employeeId = !empty(\Auth::user()->employee) ? \Auth::user()->employee->id : 0;
                            $employeeAttendance = AttendanceEmployee::orderBy('id', 'desc')
                                ->where('employee_id', '=', $employeeId)
                                ->where('date', '=', $date)
                                ->first();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching attendance: ' . $e->getMessage());
                        }

                        try {
                            $officeTime['startTime'] = Utility::getValByName('company_start_time') ?? '09:00';
                            $officeTime['endTime']   = Utility::getValByName('company_end_time') ?? '18:00';
                        } catch (\Exception $e) {
                            \Log::error('Error fetching office time: ' . $e->getMessage());
                        }

                        return view('dashboard.dashboard', compact('arrEvents', 'announcements', 'employees', 'meetings', 'employeeAttendance', 'officeTime'));
                    } catch (\Exception $e) {
                        \Log::error('Dashboard error (employee): ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'trace' => $e->getTraceAsString()
                        ]);
                        return redirect('login')->with('error', __('An error occurred loading the dashboard.'));
                    }
                }
                else
                {
                    try {
                        $creatorId = \Auth::user()->creatorId() ?? \Auth::user()->id;
                        
                        $events = [];
                        $arrEvents = [];
                        $announcements = collect([]);
                        $emp = collect([]);
                        $user = collect([]);
                        $countEmployee = 0;
                        $countUser = 0;
                        $countTicket = 0;
                        $countOpenTicket = 0;
                        $countCloseTicket = 0;
                        $notClockIns = collect([]);
                        $accountBalance = 0;
                        $activeJob = 0;
                        $inActiveJOb = 0;
                        $totalPayee = 0;
                        $totalPayer = 0;
                        $meetings = collect([]);

                        try {
                            $events = Event::where('created_by', '=', $creatorId)->get();
                            foreach($events as $event)
                            {
                                $arr['id']        = $event['id'] ?? null;
                                $arr['title']     = $event['title'] ?? '';
                                $arr['start']     = $event['start_date'] ?? date('Y-m-d');
                                $arr['end']       = $event['end_date'] ?? date('Y-m-d');
                                $arr['className'] = $event['color'] ?? 'event-primary';
                                $arr['url']       = route('event.edit', $event['id']);
                                $arrEvents[] = $arr;
                            }
                        } catch (\Exception $e) {
                            \Log::error('Error fetching events: ' . $e->getMessage());
                        }

                        try {
                            $announcements = Announcement::orderBy('announcements.id', 'desc')
                                ->take(5)
                                ->where('created_by', '=', $creatorId)
                                ->get();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching announcements: ' . $e->getMessage());
                        }

                        try {
                            $emp = User::where('type', '=', 'employee')
                                ->where('created_by', '=', $creatorId)
                                ->get();
                            $countEmployee = count($emp);
                        } catch (\Exception $e) {
                            \Log::error('Error fetching employees: ' . $e->getMessage());
                        }

                        try {
                            $user = User::where('type', '!=', 'employee')
                                ->where('created_by', '=', $creatorId)
                                ->get();
                            $countUser = count($user);
                        } catch (\Exception $e) {
                            \Log::error('Error fetching users: ' . $e->getMessage());
                        }

                        try {
                            $countTicket = Ticket::where('created_by', '=', $creatorId)->count();
                            $countOpenTicket = Ticket::where('status', '=', 'open')
                                ->where('created_by', '=', $creatorId)
                                ->count();
                            $countCloseTicket = Ticket::where('status', '=', 'close')
                                ->where('created_by', '=', $creatorId)
                                ->count();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching tickets: ' . $e->getMessage());
                        }

                        try {
                            $currentDate = date('Y-m-d');
                            $notClockIn = AttendanceEmployee::where('date', '=', $currentDate)
                                ->get()
                                ->pluck('employee_id');
                            $notClockIns = Employee::where('created_by', '=', $creatorId)
                                ->whereNotIn('id', $notClockIn)
                                ->get();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching clock-ins: ' . $e->getMessage());
                        }

                        try {
                            $accountBalance = AccountList::where('created_by', '=', $creatorId)
                                ->sum('initial_balance') ?? 0;
                        } catch (\Exception $e) {
                            \Log::error('Error fetching account balance: ' . $e->getMessage());
                        }
                        
                        try {
                            $activeJob = Job::where('status', 'active')
                                ->where('created_by', '=', $creatorId)
                                ->count();
                            $inActiveJOb = Job::where('status', 'in_active')
                                ->where('created_by', '=', $creatorId)
                                ->count();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching jobs: ' . $e->getMessage());
                        }

                        try {
                            $totalPayee = Payees::where('created_by', '=', $creatorId)->count();
                            $totalPayer = Payer::where('created_by', '=', $creatorId)->count();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching payees/payers: ' . $e->getMessage());
                        }

                        try {
                            $meetings = Meeting::where('created_by', '=', $creatorId)
                                ->limit(5)
                                ->get();
                        } catch (\Exception $e) {
                            \Log::error('Error fetching meetings: ' . $e->getMessage());
                        }

                        return view('dashboard.dashboard', compact('arrEvents', 'announcements', 'activeJob','inActiveJOb','meetings', 'countEmployee', 'countUser', 'countTicket', 'countOpenTicket', 'countCloseTicket', 'notClockIns', 'countEmployee', 'accountBalance', 'totalPayee', 'totalPayer'));
                    } catch (\Exception $e) {
                        \Log::error('Dashboard error (company/admin): ' . $e->getMessage(), [
                            'user_id' => $user->id,
                            'trace' => $e->getTraceAsString()
                        ]);
                        return redirect('login')->with('error', __('An error occurred loading the dashboard.'));
                    }
                }
            }
            else
            {
                if(!file_exists(storage_path() . "/installed"))
                {
                    header('location:install');
                    die;
                }
                else
                {
                    return redirect('login');
                }
            }
        } catch (\Exception $e) {
            \Log::error('Dashboard fatal error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            if (Auth::check()) {
                return redirect('login')->with('error', __('An error occurred. Please try again.'));
            }
            
            return redirect('login');
        }
    }

    public function getOrderChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {
            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-2 week +1 day");
                for($i = 0; $i < 14; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('d-M', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }

        $arrTask          = [];
        $arrTask['label'] = [];
        $arrTask['data']  = [];
        foreach($arrDuration as $date => $label)
        {

            $data               = Order::select(\DB::raw('count(*) as total'))->whereDate('created_at', '=', $date)->first();
            $arrTask['label'][] = $label;
            $arrTask['data'][]  = $data->total;
        }

        return $arrTask;
    }
}
