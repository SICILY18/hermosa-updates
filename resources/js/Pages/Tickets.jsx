import React, { useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import axios from 'axios';
import DynamicTitleLayout from '@/Layouts/DynamicTitleLayout';
import BillHandlerLayout from '@/Layouts/BillHandlerLayout';
import AdminLayout from '@/Layouts/AdminLayout';
import DatePicker from 'react-datepicker';
import "react-datepicker/dist/react-datepicker.css";

const statusOptions = [
  { label: 'All Status', value: 'all' },
  { label: 'Open', value: 'open' },
  { label: 'Pending', value: 'pending' },
  { label: 'Resolved', value: 'resolved' },
  { label: 'Closed', value: 'closed' }
];

const statusUpdateOptions = [
  { label: 'Open', value: 'open' },
  { label: 'Pending', value: 'pending' },
  { label: 'Resolved', value: 'resolved' },
  { label: 'Closed', value: 'closed' }
];

const priorityOptions = [
  { label: 'Low', value: 'Low' },
  { label: 'Medium', value: 'Medium' },
  { label: 'High', value: 'High' },
  { label: 'Urgent', value: 'Urgent' }
];

const Tickets = () => {
  const [tickets, setTickets] = useState([]);
  const [filteredTickets, setFilteredTickets] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [dateFilter, setDateFilter] = useState(null);
  const [viewing, setViewing] = useState(null);
  const [remarks, setRemarks] = useState('');
  const [loading, setLoading] = useState(true);
  const [viewingRemarks, setViewingRemarks] = useState(null);
  const [error, setError] = useState('');

  // Determine layout based on path
  const isBillHandler = typeof window !== 'undefined' && window.location.pathname.startsWith('/bill-handler');
  const Layout = isBillHandler ? BillHandlerLayout : AdminLayout;
  const userRole = isBillHandler ? 'bill handler' : 'admin';

  // Fetch tickets from API
  useEffect(() => {
    fetchTickets();
  }, []);

  const fetchTickets = async () => {
    try {
      setLoading(true);
      
      // Check authentication first
      const authResponse = await axios.get('/api/check-auth');
      if (!authResponse.data.authenticated) {
        window.location.href = '/';
        return;
      }
      
      const response = await axios.get('/api/tickets');
      if (response.data.success) {
        setTickets(response.data.data);
      } else {
        console.error('Failed to fetch tickets:', response.data.message);
        setError('Failed to load tickets: ' + response.data.message);
      }
    } catch (error) {
      console.error('Error fetching tickets:', error);
      
      // Handle authentication errors
      if (error.response?.status === 401) {
        console.log('User not authenticated, redirecting to login');
        window.location.href = '/';
        return;
      }
      
      // Handle other errors
      setError('Failed to load tickets. Please refresh the page.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    let filtered = [...tickets];

    // Apply search filter
    if (searchQuery) {
      filtered = filtered.filter(ticket =>
        ticket.subject?.toLowerCase().includes(searchQuery.toLowerCase()) ||
        ticket.ticket_reference?.toLowerCase().includes(searchQuery.toLowerCase()) ||
        ticket.ticket_id?.toString().includes(searchQuery)
      );
    }

    // Apply status filter
    if (statusFilter !== 'all') {
      filtered = filtered.filter(ticket =>
        ticket.status?.toLowerCase() === statusFilter.toLowerCase()
      );
    }

    // Apply date filter
    if (dateFilter) {
      const filterDate = new Date(dateFilter);
      filtered = filtered.filter(ticket => {
        const ticketDate = new Date(ticket.created_at);
        return ticketDate.toDateString() === filterDate.toDateString();
      });
    }

    setFilteredTickets(filtered);
  }, [searchQuery, statusFilter, dateFilter, tickets]);

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getStatusBadge = (status) => {
    const statusClass = {
      'open': 'bg-blue-100 text-blue-800',
      'pending': 'bg-yellow-100 text-yellow-800',
      'resolved': 'bg-green-100 text-green-800',
      'closed': 'bg-gray-100 text-gray-800',
      'In Progress': 'bg-purple-100 text-purple-800'
    };

    return (
      <span className={`px-2 py-1 rounded-full text-xs font-semibold capitalize ${statusClass[status] || 'bg-gray-100 text-gray-800'}`}>
        {status}
      </span>
    );
  };

  const getPriorityBadge = (priority) => {
    const priorityClass = {
      'Low': 'bg-green-100 text-green-800',
      'Medium': 'bg-yellow-100 text-yellow-800',
      'High': 'bg-orange-100 text-orange-800',
      'Urgent': 'bg-red-100 text-red-800'
    };

    return (
      <span className={`px-2 py-1 rounded-full text-xs font-semibold ${priorityClass[priority] || 'bg-gray-100 text-gray-800'}`}>
        {priority}
      </span>
    );
  };

  const handleStatusUpdate = async (ticketReference, newStatus) => {
    try {
      console.log('Updating ticket:', ticketReference, 'with status:', newStatus, 'and remarks:', remarks);
      
      const updateData = {
        status: newStatus,
        ticket_remarks: remarks
      };

      const response = await axios.put(`/api/tickets/ref/${encodeURIComponent(ticketReference)}`, updateData);
      
      console.log('Update response:', response.data);
      
      if (response.data.success) {
        // Show success message
        alert('Ticket updated successfully!');
        
        // Refresh tickets list
        await fetchTickets();
        setViewing(null);
        setRemarks('');
      } else {
        throw new Error(response.data.message || 'Update failed');
      }
    } catch (error) {
      console.error('Error updating ticket:', error);
      
      let errorMessage = 'Failed to update ticket';
      
      if (error.response) {
        // Server responded with error status
        console.error('Server error:', error.response.data);
        errorMessage = error.response.data.message || `Server error: ${error.response.status}`;
      } else if (error.request) {
        // Request was made but no response received
        console.error('Network error:', error.request);
        errorMessage = 'Network error - please check your connection';
      } else {
        // Something else happened
        console.error('Error:', error.message);
        errorMessage = error.message;
      }
      
      alert(errorMessage);
    }
  };

  const viewRemarksHistory = (ticket) => {
    let history = [];
    if (ticket.remarks_history) {
      try {
        history = typeof ticket.remarks_history === 'string' 
          ? JSON.parse(ticket.remarks_history) 
          : ticket.remarks_history;
      } catch (e) {
        console.error('Error parsing remarks history:', e);
      }
    }
    setViewingRemarks({ ticket, history });
  };

  return (
    <DynamicTitleLayout userRole={userRole}>
      <Layout>
        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
          <h1 className="text-xl font-semibold">Tickets</h1>
        </div>
        
        {/* Filters Section */}
        <div className="bg-white rounded-xl shadow-md p-4 mb-3">
          <div className="grid grid-cols-1 md:grid-cols-3 gap-2">
            {/* Search */}
            <div className="flex flex-col w-full max-w-[350px] min-w-[200px]">
              <label className="block text-sm font-medium text-gray-700 mb-1">Search Tickets</label>
              <input
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Search by ID, reference, or subject..."
                className="w-full max-w-[350px] min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            {/* Status Filter */}
            <div className="flex flex-col w-full max-w-[350px] min-w-[200px]">
              <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full max-w-[350px] min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                {statusOptions.map(option => (
                  <option key={option.value} value={option.value}>
                    {option.label}
                  </option>
                ))}
              </select>
            </div>
            {/* Date Filter */}
            <div className="flex flex-col w-full max-w-[350px] min-w-[200px]">
              <label className="block text-sm font-medium text-gray-700 mb-1">Date</label>
              <DatePicker
                selected={dateFilter}
                onChange={date => setDateFilter(date)}
                className="w-full max-w-[350px] min-w-[200px] px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholderText="Select date"
                dateFormat="MMMM d, yyyy"
              />
            </div>
          </div>
        </div>
        
        {/* Error Display */}
        {error && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
            <div className="flex">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <p className="text-sm text-red-700">{error}</p>
                <button 
                  onClick={() => {setError(''); fetchTickets();}}
                  className="mt-2 text-sm bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded"
                >
                  Retry
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Tickets Table */}
        <div className="bg-white rounded-xl shadow-md p-6">
          <table className="min-w-full text-sm text-left">
            <thead>
              <tr className="border-b">
                <th className="py-2 px-4 font-semibold min-w-[180px]">Ticket Reference</th>
                <th className="py-2 px-4 font-semibold min-w-[200px] max-w-[300px]">Subject</th>
                <th className="py-2 px-4 font-semibold min-w-[120px]">Category</th>
                <th className="py-2 px-4 font-semibold min-w-[120px]">Status</th>
                <th className="py-2 px-4 font-semibold min-w-[100px]">Priority</th>
                <th className="py-2 px-4 font-semibold min-w-[180px]">Created</th>
                <th className="py-2 px-4 font-semibold min-w-[120px]">Action</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan="7" className="py-4 text-center">
                    <div className="flex items-center justify-center">
                      <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                      <span className="ml-2">Loading tickets...</span>
                    </div>
                  </td>
                </tr>
              ) : filteredTickets.length === 0 ? (
                <tr>
                  <td colSpan="7" className="py-4 text-center text-gray-500">
                    No tickets found
                  </td>
                </tr>
              ) : (
                filteredTickets.map(ticket => (
                  <tr key={ticket.ticket_id || ticket.id} className="border-b hover:bg-blue-50">
                    <td className="py-2 px-4 min-w-[180px]">{ticket.ticket_reference || 'N/A'}</td>
                    <td className="py-2 px-4 min-w-[200px] max-w-[300px] truncate">{ticket.subject}</td>
                    <td className="py-2 px-4 min-w-[120px]">{ticket.category}</td>
                    <td className="py-2 px-4 min-w-[120px]">{getStatusBadge(ticket.status)}</td>
                    <td className="py-2 px-4 min-w-[100px]">{getPriorityBadge(ticket.priority)}</td>
                    <td className="py-2 px-4 min-w-[180px]">{formatDate(ticket.created_at)}</td>
                    <td className="py-2 px-4 min-w-[120px]">
                      <div className="flex gap-1">
                        <button 
                          onClick={() => setViewing(ticket)} 
                          className="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 text-xs"
                        >
                          Update
                        </button>
                        <button 
                          onClick={() => viewRemarksHistory(ticket)} 
                          className="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 text-xs"
                        >
                          History
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {/* Update Ticket Modal */}
        {viewing && (
          <div className="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-lg p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
              <h2 className="text-xl font-bold mb-4">Update Ticket</h2>
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                  <p className="text-gray-900">{viewing.ticket_reference}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                  <p className="text-gray-900">{viewing.subject}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Category</label>
                  <p className="text-gray-900">{viewing.category} - {viewing.subcategory}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <p className="text-gray-900 bg-gray-50 p-2 rounded">{viewing.description}</p>
                </div>
                {/* Image Display */}
                {viewing.image_url && (
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Uploaded Image</label>
                    <div className="border rounded-lg p-2 bg-gray-50">
                      <img 
                        src={viewing.image_url} 
                        alt="Ticket attachment"
                        className="max-w-full h-auto max-h-64 rounded cursor-pointer hover:opacity-80"
                        onClick={() => window.open(viewing.image_url, '_blank')}
                        onError={(e) => {
                          console.error('Image failed to load:', viewing.image_url);
                          e.target.style.display = 'none';
                          e.target.nextSibling.style.display = 'block';
                        }}
                        onLoad={() => {
                          console.log('Image loaded successfully:', viewing.image_url);
                        }}
                      />
                      <div className="text-red-500 text-sm mt-2" style={{display: 'none'}}>
                        <p>⚠️ Image could not be loaded</p>
                        <p className="text-xs text-gray-500 break-all">URL: {viewing.image_url}</p>
                        <button 
                          onClick={() => window.open(viewing.image_url, '_blank')}
                          className="text-blue-500 hover:underline text-xs mt-1"
                        >
                          Try opening in new tab
                        </button>
                      </div>
                    </div>
                  </div>
                )}
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                  <select
                    value={viewing.status}
                    onChange={(e) => setViewing({...viewing, status: e.target.value})}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    {statusUpdateOptions.map(option => (
                      <option key={option.value} value={option.value}>
                        {option.label}
                      </option>
                    ))}
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Created</label>
                  <p className="text-gray-900">{formatDate(viewing.created_at)}</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Add Remarks</label>
                  <textarea
                    value={remarks}
                    onChange={(e) => setRemarks(e.target.value)}
                    placeholder="Add remarks about this ticket..."
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    rows="4"
                  />
                </div>
              </div>
              <div className="flex justify-end gap-2 mt-6">
                <button 
                  onClick={() => setViewing(null)} 
                  className="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400"
                >
                  Cancel
                </button>
                <button 
                  onClick={() => handleStatusUpdate(viewing.ticket_reference, viewing.status)} 
                  className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                  Update Ticket
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Remarks History Modal */}
        {viewingRemarks && (
          <div className="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
              <h2 className="text-xl font-bold mb-4">Remarks History</h2>
              <div className="mb-4">
                <strong>Ticket:</strong> {viewingRemarks.ticket.ticket_reference}
              </div>
              
              {viewingRemarks.history.length === 0 ? (
                <p className="text-gray-500">No remarks history available.</p>
              ) : (
                <div className="space-y-3 max-h-96 overflow-y-auto">
                  {viewingRemarks.history.map((remark, index) => (
                    <div key={index} className="border-b pb-3">
                      <div className="flex justify-between items-start mb-1">
                        <strong className="text-sm">{remark.user}</strong>
                        <span className="text-xs text-gray-500">
                          {formatDate(remark.timestamp)}
                        </span>
                      </div>
                      <p className="text-gray-700">{remark.remarks}</p>
                    </div>
                  ))}
                </div>
              )}
              
              <div className="flex justify-end mt-4">
                <button 
                  onClick={() => setViewingRemarks(null)} 
                  className="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        )}
      </Layout>
    </DynamicTitleLayout>
  );
};

export default Tickets; 