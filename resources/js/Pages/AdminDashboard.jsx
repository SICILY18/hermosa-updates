import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';
import { Line, Pie, Bar } from 'react-chartjs-2';
import axios from 'axios';
import DynamicTitleLayout from '@/Layouts/DynamicTitleLayout';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    ArcElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const AdminDashboard = () => {
    const { auth } = usePage().props;
    const [profilePicture, setProfilePicture] = useState(null);

    const categoryOptions = [
        { label: 'All', value: 'all' },
        { label: 'Commercial', value: 'commercial' },
        { label: 'Residential', value: 'residential' },
        { label: 'Government', value: 'government' },
    ];

    const [selectedCategory, setSelectedCategory] = useState('all');
    const [filteredDashboardData, setFilteredDashboardData] = useState({
        users: 0,
        totalPayments: 25000.00,
        activeUsers: 0,
        paymentsDone: 65,
        paymentsPending: 35,
        recentPayments: [
            { id: 1, name: 'Liam Payne', amount: 344.00, time: '30s ago' },
            { id: 2, name: 'Carlos Sainz', amount: 243.00, time: '20m ago' },
            { id: 3, name: 'Zayn Malik', amount: 268.00, time: '1h ago' },
        ],
        monthlyData: [4000, 5000, 3000, 6000, 4500, 5500]
    });

    const [dashboardStats, setDashboardStats] = useState({
        total_customers: 0,
        active_customers: 0,
        residential_customers: 0,
        commercial_customers: 0,
        government_customers: 0
    });

    const [searchRecent, setSearchRecent] = useState('');

    useEffect(() => {
        const fetchDashboardData = async () => {
            try {
                // Fetch profile data
                const profileResponse = await axios.get('/api/admin/profile');
                if (profileResponse.data && profileResponse.data.profile_picture) {
                    setProfilePicture(profileResponse.data.profile_picture);
                }

                // Fetch dashboard stats
                const statsResponse = await axios.get('/api/dashboard/stats');
                if (statsResponse.data && statsResponse.data.success) {
                    const stats = statsResponse.data.data;
                    setDashboardStats(stats);
                    
                    // Update the filtered dashboard data with real customer count
                    setFilteredDashboardData(prev => ({
                        ...prev,
                        users: stats.total_customers,
                        activeUsers: stats.active_customers
                    }));
                }
            } catch (error) {
                console.error('Error fetching dashboard data:', error);
            }
        };

        fetchDashboardData();
    }, []);

    useEffect(() => {
        // Update dashboard data based on selected category
        if (selectedCategory === 'all') {
            setFilteredDashboardData(prev => ({
                ...prev,
                users: dashboardStats.total_customers,
                activeUsers: dashboardStats.active_customers,
            }));
        } else {
            // Filter by specific customer type
            let filteredCount = 0;
            switch (selectedCategory) {
                case 'commercial':
                    filteredCount = dashboardStats.commercial_customers;
                    break;
                case 'residential':
                    filteredCount = dashboardStats.residential_customers;
                    break;
                case 'government':
                    filteredCount = dashboardStats.government_customers;
                    break;
                default:
                    filteredCount = dashboardStats.total_customers;
            }
            
            setFilteredDashboardData(prev => ({
                ...prev,
                users: filteredCount,
                activeUsers: filteredCount, // Assuming filtered customers are active
                monthlyData: prev.monthlyData.map((val, idx) => val - (idx + 1) * 100),
                recentPayments: prev.recentPayments.filter((_, idx) => idx % 2 === 0),
            }));
        }
    }, [selectedCategory, dashboardStats]);

    const lineChartData = {
        labels: ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN'],
        datasets: [
            {
                data: filteredDashboardData.monthlyData,
                fill: true,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
            },
        ],
    };

    const lineChartOptions = {
        responsive: true,
        plugins: {
            legend: {
                display: false,
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.1)',
                },
            },
            x: {
                grid: {
                    display: false,
                },
            },
        },
    };

    const barChartData = {
        labels: ['Payments Done', 'Payments Pending'],
        datasets: [
            {
                data: [filteredDashboardData.paymentsDone, filteredDashboardData.paymentsPending],
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(191, 219, 254)',
                ],
                borderWidth: 0,
            },
        ],
    };

    const barChartOptions = {
        responsive: true,
        plugins: {
            legend: {
                display: false,
            },
        },
        scales: {
            y: {
                beginAtZero: true,
            },
        },
    };

    return (
        <DynamicTitleLayout userRole="admin">
            <div className="min-h-screen bg-[#60B5FF] font-[Poppins] overflow-x-hidden">
                {/* Sidebar */}
                <div className="fixed left-0 top-0 h-full w-[240px] bg-white shadow-lg transform transition-transform duration-200 lg:translate-x-0 md:translate-x-0 -translate-x-full flex flex-col">
                    <div className="p-3 flex-shrink-0">
                        <img src="https://i.postimg.cc/fTdMBwmQ/hermosa-logo.png" alt="Logo" className="w-50 h-50 mx-auto mb-3" />
                    </div>
                    <nav className="flex flex-col flex-1 overflow-y-auto">
                        <div className="flex-1 pb-4">
                            <Link href="/admin/dashboard" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/dashboard' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">dashboard</span>
                                Dashboard
                            </Link>
                            <Link href="/admin/announcement" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/announcement' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">campaign</span>
                                Announcement
                            </Link>
                            <Link href="/admin/accounts" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/accounts' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">manage_accounts</span>
                                Manage Accounts
                            </Link>
                            <Link href="/admin/rate-management" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/rate-management' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">price_change</span>
                                Rate Management
                            </Link>
                            <Link href="/admin/payment" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/payment' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">payments</span>
                                Payment
                            </Link>
                            <Link href="/admin/reports" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/reports' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">description</span>
                                Reports
                            </Link>
                            <Link href="/admin/tickets" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/tickets' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">confirmation_number</span>
                                Tickets
                            </Link>
                            <Link href="/admin/profile" className={`flex items-center px-6 py-3 text-base ${window.location.pathname === '/admin/profile' ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:bg-gray-50'}`}>
                                <span className="material-symbols-outlined mr-3">person</span>
                                Profile
                            </Link>
                        </div>
                        <div className="flex-shrink-0">
                            <button
                                onClick={async () => {
                                    if (window.confirm('Are you sure you want to log out?')) {
                                        try {
                                            await axios.get('/sanctum/csrf-cookie');
                                            await axios.post('/admin/logout');
                                            window.location.href = '/';
                                        } catch (error) {
                                            window.location.href = '/';
                                        }
                                    }
                                }}
                                className="flex items-center px-6 py-3 text-base text-gray-600 hover:text-red-600 hover:bg-red-50 w-full text-left"
                            >
                                <span className="material-symbols-outlined mr-3">logout</span>
                                Logout
                            </button>
                        </div>
                    </nav>
                </div>

                {/* Mobile Header */}
                <div className="lg:hidden fixed top-0 left-0 right-0 bg-white h-14 flex items-center justify-between px-4 z-20">
                    <button className="text-gray-600 hover:text-gray-800">
                        <span className="material-symbols-outlined">menu</span>
                    </button>
                    <img src="https://i.postimg.cc/fTdMBwmQ/hermosa-logo.png" alt="Logo" className="h-8" />
                    <div></div>
                </div>

                {/* Main Content */}
                <div className="lg:ml-[240px] p-3 sm:p-4 md:p-6 lg:p-6 pt-16 lg:pt-6">
                    {/* Header */}
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                        <h1 className="text-xl font-semibold">Dashboard</h1>
                        <div className="flex items-center w-full sm:w-auto gap-2">
                            <select
                                value={selectedCategory}
                                onChange={e => setSelectedCategory(e.target.value)}
                                className="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                {categoryOptions.map(option => (
                                    <option key={option.value} value={option.value}>{option.label}</option>
                                ))}
                            </select>
                            <Link href="/admin/profile">
                                <img 
                                    src={profilePicture || `https://ui-avatars.com/api/?name=${auth?.user?.name || 'Admin'}&background=0D8ABC&color=fff`}
                                    alt="Profile" 
                                    className="w-8 h-8 rounded-full cursor-pointer hover:opacity-80 transition-opacity object-cover"
                                />
                            </Link>
                        </div>
                    </div>

                    {/* Stats Cards */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div className="bg-white p-4 rounded-lg shadow">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-gray-500">Users</p>
                                    <h2 className="text-lg font-semibold">{filteredDashboardData.users}</h2>
                                </div>
                                <span className="material-symbols-outlined text-blue-500 bg-blue-100 p-2 rounded-full text-[20px]">group</span>
                            </div>
                        </div>
                        <div className="bg-white p-4 rounded-lg shadow">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-gray-500">Total Payments</p>
                                    <h2 className="text-lg font-semibold">₱{filteredDashboardData.totalPayments.toLocaleString()}</h2>
                                </div>
                                <span className="material-symbols-outlined text-green-500 bg-green-100 p-2 rounded-full text-[20px]">payments</span>
                            </div>
                        </div>
                        <div className="bg-white p-4 rounded-lg shadow">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-xs text-gray-500">Active Users</p>
                                    <h2 className="text-lg font-semibold">{filteredDashboardData.activeUsers}</h2>
                                </div>
                                <span className="material-symbols-outlined text-purple-500 bg-purple-100 p-2 rounded-full text-[20px]">person</span>
                            </div>
                        </div>
                    </div>

                    {/* Charts Section */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                        {/* Monthly Payments Chart */}
                        <div className="bg-white p-4 rounded-lg shadow">
                            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4">
                                <h3 className="text-sm font-semibold mb-2 sm:mb-0">This Month</h3>
                                <p className="text-lg font-bold">₱25,000.00</p>
                            </div>
                            <div className="h-40">
                                <Line data={{
                                    ...lineChartData,
                                    datasets: [
                                        {
                                            ...lineChartData.datasets[0],
                                            data: filteredDashboardData.monthlyData,
                                        },
                                    ],
                                }} options={lineChartOptions} />
                            </div>
                        </div>

                        {/* Payment Analysis Bar Chart */}
                        <div className="bg-white p-4 rounded-lg shadow">
                            <h3 className="text-sm font-semibold mb-4">Payment Analysis</h3>
                            <div className="flex items-center justify-center h-40">
                                <Bar data={{
                                    ...barChartData,
                                    datasets: [
                                        {
                                            ...barChartData.datasets[0],
                                            data: [filteredDashboardData.paymentsDone, filteredDashboardData.paymentsPending],
                                        },
                                    ],
                                }} options={barChartOptions} />
                            </div>
                            <div className="flex flex-col sm:flex-row justify-center gap-3 sm:gap-6 mt-4">
                                <div className="flex items-center">
                                    <div className="w-2.5 h-2.5 rounded-full bg-blue-500 mr-2"></div>
                                    <span className="text-xs">Payments Done ({filteredDashboardData.paymentsDone}%)</span>
                                </div>
                                <div className="flex items-center">
                                    <div className="w-2.5 h-2.5 rounded-full bg-blue-200 mr-2"></div>
                                    <span className="text-xs">Payments Pending ({filteredDashboardData.paymentsPending}%)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Recent Payments Table */}
                    <div className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="flex items-center justify-between p-4 border-b">
                            <h3 className="text-sm font-semibold">Recent Payments</h3>
                            <div className="flex items-center gap-2">
                                <div className="relative max-w-xs">
                                    <span className="material-symbols-outlined absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm">search</span>
                                    <input
                                        type="text"
                                        value={searchRecent}
                                        onChange={e => setSearchRecent(e.target.value)}
                                        placeholder="Search recent payments by user"
                                        className="w-full pl-8 pr-3 py-1.5 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        style={{ minWidth: 180 }}
                                    />
                                </div>
                                <Link href="/payments" className="text-blue-500 text-xs hover:underline ml-2">see all</Link>
                            </div>
                        </div>
                        <div className="p-4">
                            {filteredDashboardData.recentPayments
                                .filter(payment => payment.name.toLowerCase().includes(searchRecent.toLowerCase()))
                                .map((payment) => (
                                    <div key={payment.id} className="flex flex-col sm:flex-row items-start sm:items-center justify-between py-2 gap-2">
                                        <div className="flex items-center">
                                            <img 
                                                src={`https://ui-avatars.com/api/?name=${payment.name}&background=0D8ABC&color=fff`} 
                                                alt={payment.name} 
                                                className="w-8 h-8 rounded-full mr-3" 
                                            />
                                            <span className="text-sm">{payment.name}</span>
                                        </div>
                                        <div className="flex items-center gap-4">
                                            <span className="text-sm font-semibold">₱{payment.amount.toFixed(2)}</span>
                                            <span className="text-xs text-gray-500">{payment.time}</span>
                                        </div>
                                    </div>
                                ))}
                        </div>
                    </div>
                </div>
            </div>
        </DynamicTitleLayout>
    );
};

export default AdminDashboard;
