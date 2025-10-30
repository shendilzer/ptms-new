<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, ref, computed } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

interface OperatorStats {
  total_operators: number;
  total_drivers: number;
  total_motorcycles: number;
  total_toda: number;
  new_permits: number;
  renew_permits: number;
  retire_permits: number;
}

const stats = ref<OperatorStats>({
  total_operators: 0,
  total_drivers: 0,
  total_motorcycles: 0,
  total_toda: 0,
  new_permits: 0,
  renew_permits: 0,
  retire_permits: 0,
});

interface Operator {
  id: number;
  fullname: string;
  email_address: string;
  contact_number: string;
  driver_fullname: string | null;
  plate_number: string | null;
  mtop_number: string;
  toda_name: string | null;
  date_registered: string;
  permit_status: string;
  date_last_paid: string | null;
  address?: string;
  birth_date?: string;
  gender?: string;
  driver_license?: string | null;
  motorcycle_model?: string | null;
  motorcycle_color?: string | null;
  engine_number?: string | null;
  chassis_number?: string | null;
}

const operators = ref<Operator[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

// Data Table
const searchTerm = ref('');
const currentPage = ref(1);
const perPage = ref(10);

// Print Modals
const showPrintModal = ref(false);
const showIdPrintModal = ref(false);
const showReportModal = ref(false);
const showPermitReportModal = ref(false);
const showIdReportModal = ref(false);
const selectedOperator = ref<Operator | null>(null);
const printLoading = ref(false);
const idPrintLoading = ref(false);
const bodyNumber = ref('');
const stickerNumber = ref('');
const orNumber = ref('');

// Report Logs
interface PrintLog {
  id: string;
  type: 'permit' | 'id_card';
  operator_name: string;
  driver_name: string;
  mtop_number: string;
  or_number?: string;
  body_number?: string;
  sticker_number?: string;
  quarter: string;
  printed_by: string;
  printed_at: string;
  timestamp: number;
}

const printLogs = ref<PrintLog[]>([]);
const reportLoading = ref(false);
const reportDateRange = ref({
  start: new Date().toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0]
});

const fetchStats = async () => {
  try {
    loading.value = true;
    error.value = null;

    console.log('Fetching dashboard data...');

    const [statsResponse, operatorsResponse] = await Promise.all([
      axios.get('/dashboard/operator-stats'),
      axios.get('/dashboard/recent-operators')
    ]);

    console.log('Stats response:', statsResponse.data);
    console.log('Operators response:', operatorsResponse.data);

    if (statsResponse.data && !statsResponse.data.error) {
      stats.value = {
        total_operators: statsResponse.data.total_operators || 0,
        total_drivers: statsResponse.data.total_drivers || 0,
        total_motorcycles: statsResponse.data.total_motorcycles || 0,
        total_toda: statsResponse.data.total_toda || 0,
        new_permits: statsResponse.data.new_permits || 0,
        renew_permits: statsResponse.data.renew_permits || 0,
        retire_permits: statsResponse.data.retire_permits || 0,
      };
    } else if (statsResponse.data?.error) {
      throw new Error(statsResponse.data.message || statsResponse.data.error);
    }

    if (operatorsResponse.data && !operatorsResponse.data.error) {
      if (Array.isArray(operatorsResponse.data)) {
        operators.value = operatorsResponse.data;
      } else if (operatorsResponse.data.data && Array.isArray(operatorsResponse.data.data)) {
        operators.value = operatorsResponse.data.data;
      } else {
        operators.value = [];
      }
    } else if (operatorsResponse.data?.error) {
      throw new Error(operatorsResponse.data.message || operatorsResponse.data.error);
    }

    console.log('Processed operators:', operators.value);

  } catch (err: any) {
    console.error('Error fetching dashboard stats:', err);
    error.value = err.response?.data?.message || err.message || 'Failed to load dashboard data';

    try {
      console.log('Trying fallback endpoints...');
      const overviewResponse = await axios.get('/dashboard/overview');
      if (overviewResponse.data?.operator_stats) {
        stats.value = {
          total_operators: overviewResponse.data.operator_stats.total_operators || 0,
          total_drivers: overviewResponse.data.operator_stats.total_drivers || 0,
          total_motorcycles: overviewResponse.data.operator_stats.total_motorcycles || 0,
          total_toda: overviewResponse.data.operator_stats.total_toda || 0,
          new_permits: overviewResponse.data.operator_stats.new_permits || 0,
          renew_permits: overviewResponse.data.operator_stats.renew_permits || 0,
          retire_permits: overviewResponse.data.operator_stats.retire_permits || 0,
        };
      }
      if (overviewResponse.data?.recent_operators) {
        operators.value = overviewResponse.data.recent_operators;
      }
    } catch (fallbackErr) {
      console.error('Fallback also failed:', fallbackErr);
    }
  } finally {
    loading.value = false;
  }
};

const fetchOperatorsDirectly = async () => {
  try {
    console.log('Fetching operators directly...');
    const response = await axios.get('/operators/list?per_page=100&with_relations=true');
    console.log('Direct operators response:', response.data);

    if (response.data && response.data.data) {
      operators.value = response.data.data.map((operator: any) => ({
        id: operator.id,
        fullname: operator.fullname,
        email_address: operator.email_address,
        contact_number: operator.contact_number,
        driver_fullname: operator.driver?.fullname || null,
        plate_number: operator.motorcycle?.plate_number || null,
        mtop_number: operator.mtop_number,
        toda_name: operator.toda?.name || null,
        date_registered: operator.created_at,
        permit_status: operator.permit_status,
        date_last_paid: operator.date_last_paid,
        address: operator.address,
        birth_date: operator.birth_date,
        gender: operator.gender,
        driver_license: operator.driver?.license_number || null,
        motorcycle_model: operator.motorcycle?.model || null,
        motorcycle_color: operator.motorcycle?.color || null,
        engine_number: operator.motorcycle?.engine_number || null,
        chassis_number: operator.motorcycle?.chassis_number || null,
      }));
    } else if (Array.isArray(response.data)) {
      operators.value = response.data;
    }
  } catch (err) {
    console.error('Failed to fetch operators directly:', err);
  }
};

const displayValue = (value: string | null | undefined, fallback: string = 'N/A') => {
  return value && value !== 'null' && value !== 'undefined' ? value : fallback;
};

const filteredData = computed(() => {
  if (!searchTerm.value) return operators.value;
  return operators.value.filter((item) =>
    Object.values(item).some((val) =>
      String(val).toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  );
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;
  return filteredData.value.slice(start, end);
});

const totalPages = computed(() => Math.ceil(filteredData.value.length / perPage.value));

const getStatusColor = (status: string) => {
  const colors = {
    new: 'bg-green-500 text-white',
    renew: 'bg-blue-500 text-white',
    retire: 'bg-red-500 text-white'
  };
  return colors[status as keyof typeof colors] || 'bg-gray-500 text-white';
};

const getStatusText = (status: string) => {
  const texts = {
    new: 'New',
    renew: 'Renewed',
    retire: 'Retired'
  };
  return texts[status as keyof typeof texts] || status;
};

// Get current quarter based on current date
const getCurrentQuarter = () => {
  const currentMonth = new Date().getMonth();
  if (currentMonth >= 0 && currentMonth <= 2) return '1st Quarter';
  if (currentMonth >= 3 && currentMonth <= 5) return '2nd Quarter';
  if (currentMonth >= 6 && currentMonth <= 8) return '3rd Quarter';
  return '4th Quarter';
};

// Get quarter from a specific date
const getQuarterFromDate = (dateString: string) => {
  const date = new Date(dateString);
  const month = date.getMonth();
  if (month >= 0 && month <= 2) return '1st Quarter';
  if (month >= 3 && month <= 5) return '2nd Quarter';
  if (month >= 6 && month <= 8) return '3rd Quarter';
  return '4th Quarter';
};

// Get quarter badge color
const getQuarterBadgeColor = (quarter: string) => {
  const colors = {
    '1st Quarter': 'bg-blue-100 text-blue-800 border-blue-300',
    '2nd Quarter': 'bg-green-100 text-green-800 border-green-300',
    '3rd Quarter': 'bg-yellow-100 text-yellow-800 border-yellow-300',
    '4th Quarter': 'bg-purple-100 text-purple-800 border-purple-300'
  };
  return colors[quarter as keyof typeof colors] || 'bg-gray-100 text-gray-800 border-gray-300';
};

// Print Logging Functions
const addPrintLog = (type: 'permit' | 'id_card', operator: Operator, orNumber?: string, bodyNumber?: string, stickerNumber?: string) => {
  const log: PrintLog = {
    id: Date.now().toString() + Math.random().toString(36).substr(2, 9),
    type,
    operator_name: operator.fullname,
    driver_name: operator.driver_fullname || 'N/A',
    mtop_number: operator.mtop_number,
    or_number: orNumber,
    body_number: bodyNumber,
    sticker_number: stickerNumber,
    quarter: getQuarterFromDate(operator.date_last_paid || operator.date_registered),
    printed_by: 'System User',
    printed_at: new Date().toLocaleString('en-PH', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    }),
    timestamp: Date.now()
  };

  printLogs.value.unshift(log);

  // Save to localStorage for persistence
  const allLogs = JSON.parse(localStorage.getItem('maramag_print_logs') || '[]');
  allLogs.unshift(log);
  localStorage.setItem('maramag_print_logs', JSON.stringify(allLogs.slice(0, 1000)));
};

const loadPrintLogs = () => {
  const savedLogs = localStorage.getItem('maramag_print_logs');
  if (savedLogs) {
    printLogs.value = JSON.parse(savedLogs);
  }
};

// Delete log function
const deleteLog = (logId: string) => {
  if (confirm('Are you sure you want to delete this print log?')) {
    printLogs.value = printLogs.value.filter(log => log.id !== logId);

    // Update localStorage
    const allLogs = JSON.parse(localStorage.getItem('maramag_print_logs') || '[]');
    const updatedLogs = allLogs.filter((log: PrintLog) => log.id !== logId);
    localStorage.setItem('maramag_print_logs', JSON.stringify(updatedLogs));
  }
};

// Clear all logs function
const clearAllLogs = () => {
  if (confirm('Are you sure you want to delete ALL print logs? This action cannot be undone.')) {
    printLogs.value = [];
    localStorage.setItem('maramag_print_logs', '[]');
  }
};

const filteredLogs = computed(() => {
  const startDate = new Date(reportDateRange.value.start);
  const endDate = new Date(reportDateRange.value.end);
  endDate.setHours(23, 59, 59, 999);

  return printLogs.value.filter(log => {
    const logDate = new Date(log.timestamp);
    return logDate >= startDate && logDate <= endDate;
  });
});

const filteredPermitLogs = computed(() => {
  return filteredLogs.value.filter(log => log.type === 'permit');
});

const filteredIdLogs = computed(() => {
  return filteredLogs.value.filter(log => log.type === 'id_card');
});

const getPrintStats = computed(() => {
  const filtered = filteredLogs.value;
  const permitCount = filtered.filter(log => log.type === 'permit').length;
  const idCardCount = filtered.filter(log => log.type === 'id_card').length;

  return {
    total: filtered.length,
    permits: permitCount,
    id_cards: idCardCount,
    date_range: `${new Date(reportDateRange.value.start).toLocaleDateString()} - ${new Date(reportDateRange.value.end).toLocaleDateString()}`
  };
});

const getPermitStats = computed(() => {
  const filtered = filteredPermitLogs.value;
  return {
    total: filtered.length,
    date_range: `${new Date(reportDateRange.value.start).toLocaleDateString()} - ${new Date(reportDateRange.value.end).toLocaleDateString()}`
  };
});

const getIdStats = computed(() => {
  const filtered = filteredIdLogs.value;
  return {
    total: filtered.length,
    date_range: `${new Date(reportDateRange.value.start).toLocaleDateString()} - ${new Date(reportDateRange.value.end).toLocaleDateString()}`
  };
});

// Print Functions - UPDATED WITH RETIREMENT CHECK
const openPrintModal = (operator: Operator) => {
  // Check if operator is retired
  if (operator.permit_status === 'retire') {
    alert('Cannot print permit: This operator is currently retired. Please renew the permit first.');
    return;
  }

  selectedOperator.value = operator;
  orNumber.value = '';
  showPrintModal.value = true;
};

const openIdPrintModal = (operator: Operator) => {
  // Check if operator is retired
  if (operator.permit_status === 'retire') {
    alert('Cannot print ID card: This operator is currently retired. Please renew the permit first.');
    return;
  }

  selectedOperator.value = operator;
  bodyNumber.value = '';
  stickerNumber.value = '';
  showIdPrintModal.value = true;
};

const openReportModal = () => {
  loadPrintLogs();
  showReportModal.value = true;
};

const openPermitReportModal = () => {
  loadPrintLogs();
  showPermitReportModal.value = true;
};

const openIdReportModal = () => {
  loadPrintLogs();
  showIdReportModal.value = true;
};

const closePrintModal = () => {
  showPrintModal.value = false;
  showIdPrintModal.value = false;
  showReportModal.value = false;
  showPermitReportModal.value = false;
  showIdReportModal.value = false;
  selectedOperator.value = null;
  printLoading.value = false;
  idPrintLoading.value = false;
  bodyNumber.value = '';
  stickerNumber.value = '';
  orNumber.value = '';
};

const printPermit = () => {
  if (!selectedOperator.value || !orNumber.value.trim()) {
    alert('Please enter OR Number before printing the permit.');
    return;
  }

  // Additional retirement check
  if (selectedOperator.value.permit_status === 'retire') {
    alert('Cannot print permit: This operator is currently retired. Please renew the permit first.');
    return;
  }

  printLoading.value = true;

  setTimeout(() => {
    try {
      const operator = selectedOperator.value!;

      // Add to print log
      addPrintLog('permit', operator, orNumber.value);

      const operatorName = operator.fullname;
      const contactNumber = operator.contact_number;
      const email = operator.email_address;
      const driverName = operator.driver_fullname || 'N/A';
      const plateNumber = operator.plate_number || 'N/A';
      const todaName = operator.toda_name || 'N/A';
      const permitStatus = getStatusText(operator.permit_status);
      const dateRegistered = new Date(operator.date_registered).toLocaleDateString();
      const mtopNumber = operator.mtop_number;

      const currentDate = new Date().toLocaleDateString();
      const currentYear = new Date().getFullYear();
      const validUntil = new Date(currentYear, 11, 31).toLocaleDateString();

      // Determine quarter based on last payment date or registration date
      const quarterDate = operator.date_last_paid || operator.date_registered;
      const currentQuarter = getQuarterFromDate(quarterDate);

      const getStatusColors = (status: string) => {
        switch (status) {
          case 'New':
            return {
              primaryColor: '#1e40af',
              secondaryColor: '#dbeafe',
              borderColor: '#1e40af',
              backgroundColor: '#f0f9ff',
              statusColor: '#1e40af'
            };
          case 'Renewed':
            return {
              primaryColor: '#166534',
              secondaryColor: '#dcfce7',
              borderColor: '#166534',
              backgroundColor: '#f0fdf4',
              statusColor: '#166534'
            };
          case 'Retired':
            return {
              primaryColor: '#991b1b',
              secondaryColor: '#fee2e2',
              borderColor: '#991b1b',
              backgroundColor: '#fef2f2',
              statusColor: '#991b1b'
            };
          default:
            return {
              primaryColor: '#1e40af',
              secondaryColor: '#dbeafe',
              borderColor: '#1e40af',
              backgroundColor: '#f0f9ff',
              statusColor: '#1e40af'
            };
        }
      };

      const colors = getStatusColors(permitStatus);
      const isRetired = permitStatus === 'Retired';

      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>Permit - ${operatorName}</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
              @page {
                size: A4;
                margin: 0;
              }

              * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
              }

              body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
                background: white;
                color: black;
                font-size: 12px;
                line-height: 1.3;
                width: 210mm;
                height: 297mm;
                position: relative;
              }

              .permit-container {
                width: 100%;
                height: 100%;
                padding: 15mm;
                position: relative;
                box-sizing: border-box;
                display: flex;
                flex-direction: column;
                background: white;
                border: 2mm solid ${colors.borderColor};
                overflow: hidden;
              }

              /* BACKGROUND DESIGN */
              .permit-background {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0.03;
                background:
                  linear-gradient(45deg, transparent 49%, ${colors.primaryColor} 49%, ${colors.primaryColor} 51%, transparent 51%) 0 0/20px 20px,
                  linear-gradient(-45deg, transparent 49%, ${colors.primaryColor} 49%, ${colors.primaryColor} 51%, transparent 51%) 0 0/20px 20px;
                pointer-events: none;
                z-index: -1;
              }

              .permit-background::before {
                content: "MARAMAG MUNICIPALITY";
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 48px;
                font-weight: bold;
                color: ${colors.primaryColor};
                opacity: 0.1;
                white-space: nowrap;
              }

              .permit-header {
                text-align: center;
                margin-bottom: 8mm;
                border-bottom: 1mm solid ${colors.borderColor};
                padding-bottom: 4mm;
                position: relative;
                z-index: 1;
              }

              .permit-header h1 {
                margin: 0 0 2mm 0;
                font-size: 24px;
                color: ${colors.primaryColor};
                font-weight: bold;
                text-transform: uppercase;
              }

              .permit-header h2 {
                margin: 0 0 1mm 0;
                font-size: 18px;
                color: #374151;
                font-weight: 600;
              }

              .permit-header h3 {
                margin: 0;
                font-size: 12px;
                color: #6b7280;
                font-weight: normal;
              }

              .validity-section {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 6mm;
                padding: 3mm;
                background: ${colors.secondaryColor};
                border: 1mm solid ${colors.borderColor};
                border-radius: 4px;
                position: relative;
                z-index: 1;
              }

              .issue-date, .expiry-date {
                text-align: center;
                flex: 1;
              }

              .date-label {
                font-size: 10px;
                font-weight: bold;
                color: ${colors.primaryColor};
                margin-bottom: 1mm;
                text-transform: uppercase;
              }

              .date-value {
                font-size: 12px;
                font-weight: bold;
                color: #111827;
              }

              .permit-number {
                text-align: center;
                flex: 1;
                border-left: 1mm solid ${colors.borderColor};
                border-right: 1mm solid ${colors.borderColor};
              }

              .or-number-section {
                text-align: center;
                margin: 3mm 0;
                padding: 2mm;
                background: #fff3cd;
                border: 1mm solid #ffc107;
                border-radius: 4px;
                position: relative;
                z-index: 1;
              }

              .or-number-label {
                font-size: 11px;
                font-weight: bold;
                color: #856404;
                margin-bottom: 1mm;
                text-transform: uppercase;
              }

              .or-number-value {
                font-size: 14px;
                font-weight: bold;
                color: #856404;
              }

              .status-section {
                text-align: center;
                margin: 4mm 0;
                padding: 3mm;
                background: ${colors.backgroundColor};
                border: 1mm solid ${colors.borderColor};
                border-radius: 4px;
                position: relative;
                z-index: 1;
              }

              .status-title {
                font-size: 14px;
                font-weight: bold;
                color: ${colors.primaryColor};
                margin-bottom: 2mm;
                text-transform: uppercase;
              }

              .status-value {
                font-size: 20px;
                font-weight: bold;
                margin: 2mm 0;
                color: ${colors.statusColor};
              }

              .permit-content {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 5mm;
                margin-bottom: 6mm;
                flex: 1;
                position: relative;
                z-index: 1;
              }

              .permit-section {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 4px;
                padding: 4mm;
                position: relative;
                z-index: 1;
              }

              .permit-section.full-width {
                grid-column: 1 / -1;
              }

              .permit-section h3 {
                margin: 0 0 3mm 0;
                font-size: 14px;
                color: ${colors.primaryColor};
                border-bottom: 1px solid #e5e7eb;
                padding-bottom: 2mm;
                font-weight: 600;
              }

              .permit-field {
                margin-bottom: 2mm;
                display: flex;
              }

              .permit-label {
                font-weight: bold;
                min-width: 40mm;
                color: #374151;
              }

              .permit-value {
                flex: 1;
                color: #111827;
                font-weight: 500;
              }

              .quarter-badge {
                padding: 1mm 2mm;
                border-radius: 12px;
                font-size: 10px;
                font-weight: bold;
                display: inline-block;
                border: 1px solid;
                margin-left: 2mm;
              }

              .quarter-1st {
                background: #dbeafe;
                color: #1e40af;
                border-color: #1e40af;
              }
              .quarter-2nd {
                background: #dcfce7;
                color: #166534;
                border-color: #166534;
              }
              .quarter-3rd {
                background: #fef3c7;
                color: #92400e;
                border-color: #92400e;
              }
              .quarter-4th {
                background: #e9d5ff;
                color: #7e22ce;
                border-color: #7e22ce;
              }

              .signature-area {
                margin-top: 8mm;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10mm;
                align-items: start;
                position: relative;
                z-index: 1;
              }

              .signature-box {
                text-align: center;
                padding: 3mm;
              }

              .signature-line {
                width: 100%;
                max-width: 60mm;
                height: 1px;
                border-bottom: 1mm solid #000;
                margin: 8mm auto 2mm auto;
              }

              .signature-label {
                font-size: 10px;
                font-weight: bold;
                color: #000;
              }

              .permit-footer {
                text-align: center;
                margin-top: auto;
                border-top: 1mm solid ${colors.borderColor};
                padding-top: 3mm;
                font-size: 9px;
                color: #6b7280;
                position: relative;
                z-index: 1;
              }

              .status-badge {
                padding: 2mm 3mm;
                border-radius: 12px;
                font-size: 10px;
                font-weight: bold;
                display: inline-block;
              }

              .status-new {
                background: #dbeafe;
                color: #1e40af;
                border: 1px solid #1e40af;
              }
              .status-renew {
                background: #dcfce7;
                color: #166534;
                border: 1px solid #166534;
              }
              .status-retired {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #991b1b;
              }

              .retired-notice {
                background: #fee2e2;
                border: 2mm solid #991b1b;
                border-radius: 6px;
                padding: 6mm;
                text-align: center;
                margin: 4mm 0;
                font-weight: bold;
                font-size: 18px;
                color: #991b1b;
                position: relative;
                z-index: 1;
              }

              @media print {
                body {
                  margin: 0;
                  padding: 0;
                  width: 210mm;
                  height: 297mm;
                  background: white !important;
                  -webkit-print-color-adjust: exact;
                  print-color-adjust: exact;
                }

                .permit-container {
                  padding: 12mm;
                  border: 1.5mm solid ${colors.borderColor};
                  height: 100%;
                  page-break-inside: avoid;
                }

                .no-print {
                  display: none !important;
                }
              }

              .print-controls {
                text-align: center;
                margin: 10mm;
              }

              .print-controls button {
                padding: 3mm 6mm;
                margin: 2mm;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
                font-weight: bold;
              }

              .print-btn {
                background: ${colors.primaryColor};
                color: white;
              }

              .close-btn {
                background: #6b7280;
                color: white;
              }
            </style>
          </head>
          <body>
            <div class="permit-container">
              <!-- BACKGROUND ELEMENT -->
              <div class="permit-background"></div>

              <div class="permit-header">
                <h1>MUNICIPALITY OF MARAMAG</h1>
                <h2>TRICYCLE OPERATOR'S PERMIT</h2>
                <h3>OFFICE OF THE MAYOR - MUNICIPAL PERMITS & LICENSING</h3>
              </div>

              ${isRetired ? `
                <div class="retired-notice">
                  <strong>PERMIT RETIRED - NO LONGER VALID FOR OPERATION</strong>
                </div>
              ` : `
                <div class="validity-section">
                  <div class="issue-date">
                    <div class="date-label">DATE ISSUED</div>
                    <div class="date-value">${currentDate}</div>
                  </div>
                  <div class="permit-number">
                    <div class="date-label">PERMIT NUMBER</div>
                    <div class="date-value">${mtopNumber}</div>
                  </div>
                  <div class="expiry-date">
                    <div class="date-label">VALID UNTIL</div>
                    <div class="date-value">${validUntil}</div>
                  </div>
                </div>

                <div class="or-number-section">
                  <div class="or-number-label">OFFICIAL RECEIPT NUMBER</div>
                  <div class="or-number-value">${orNumber.value}</div>
                </div>
              `}

              <div class="status-section">
                <div class="status-title">OPERATOR STATUS</div>
                <div class="status-value">
                  ${permitStatus.toUpperCase()}
                </div>
              </div>

              <div class="permit-content">
                <div class="permit-section">
                  <h3>OPERATOR INFORMATION</h3>
                  <div class="permit-field">
                    <span class="permit-label">Full Name:</span>
                    <span class="permit-value">${operatorName}</span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">Contact Number:</span>
                    <span class="permit-value">${contactNumber}</span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">Email Address:</span>
                    <span class="permit-value">${email}</span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">Date Registered:</span>
                    <span class="permit-value">${dateRegistered}</span>
                  </div>
                </div>

                <div class="permit-section">
                  <h3>DRIVER & VEHICLE INFORMATION</h3>
                  <div class="permit-field">
                    <span class="permit-label">Driver Name:</span>
                    <span class="permit-value">${driverName}</span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">Plate Number:</span>
                    <span class="permit-value">${plateNumber}</span>
                  </div>
                </div>

                <div class="permit-section full-width">
                  <h3>ASSOCIATION & PERMIT DETAILS</h3>
                  <div class="permit-field">
                    <span class="permit-label">TODA Association:</span>
                    <span class="permit-value">${todaName}</span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">MTOP Number:</span>
                    <span class="permit-value">${mtopNumber}</span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">Permit Status:</span>
                    <span class="permit-value">
                      <span class="status-badge ${permitStatus === 'New' ? 'status-new' : permitStatus === 'Renewed' ? 'status-renew' : 'status-retired'}">
                        ${permitStatus}
                      </span>
                    </span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">Current Quarter:</span>
                    <span class="permit-value">
                      <span class="quarter-badge ${currentQuarter === '1st Quarter' ? 'quarter-1st' : currentQuarter === '2nd Quarter' ? 'quarter-2nd' : currentQuarter === '3rd Quarter' ? 'quarter-3rd' : 'quarter-4th'}">
                        ${currentQuarter} ${currentYear}
                      </span>
                    </span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">Quarter Validity:</span>
                    <span class="permit-value">
                      ${currentQuarter === '1st Quarter' ? 'January 1 - March 31' :
                        currentQuarter === '2nd Quarter' ? 'April 1 - June 30' :
                        currentQuarter === '3rd Quarter' ? 'July 1 - September 30' :
                        'October 1 - December 31'}
                    </span>
                  </div>
                  <div class="permit-field">
                    <span class="permit-label">OR Number:</span>
                    <span class="permit-value" style="font-weight: bold; color: #856404;">${orNumber.value}</span>
                  </div>
                </div>
              </div>

              ${!isRetired ? `
                <div class="signature-area">
                  <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">OPERATOR'S SIGNATURE</div>
                  </div>

                  <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">ATTY. JOSE JUDE L.P. DOROMAL</div>
                    <div style="font-size: 9px; color: #666; margin-top: 1mm;">Municipal Mayor / Chairman MMTFRB</div>
                  </div>
                </div>
              ` : ''}

              <div class="permit-footer">
                <p><strong>Issued by:</strong> Municipality of Maramag - Business Permit & Licensing Office</p>
                <p>MARAMAG MUNICIPALITY • BUSINESS PERMIT & LICENSING OFFICE • www.maramag.gov.ph</p>
                <p style="margin-top: 2mm; font-size: 8px; color: #9ca3af;">
                  Permit valid for ${currentQuarter} ${currentYear}. Quarterly renewal required for continued operation.
                  ${orNumber.value ? `Official Receipt: ${orNumber.value}` : ''}
                </p>
              </div>
            </div>

            <!-- Print Controls -->
            <div class="no-print print-controls">
              <button onclick="window.print()" class="print-btn">🖨️ Print Permit</button>
              <button onclick="window.close()" class="close-btn">❌ Close Window</button>
              <p style="font-size: 10px; color: #666; margin-top: 3mm;">
                Print Size: A4 Full Page | Official Permit Document
              </p>
            </div>

            <script>
              window.onload = function() {
                setTimeout(() => {
                  window.print();
                }, 500);

                window.onafterprint = function() {
                  setTimeout(() => {
                    window.close();
                  }, 100);
                };
              };
            <\/script>
          </body>
          </html>
        `);
        printWindow.document.close();
      }
    } catch (err) {
      console.error('Error printing permit:', err);
      alert('Error generating print preview. Please try again.');
    } finally {
      printLoading.value = false;
      closePrintModal();
    }
  }, 500);
};

const printIdCard = () => {
  if (!selectedOperator.value || !bodyNumber.value.trim() || !stickerNumber.value.trim()) {
    alert('Please enter both Body Number and Sticker Number before printing.');
    return;
  }

  // Additional retirement check
  if (selectedOperator.value.permit_status === 'retire') {
    alert('Cannot print ID card: This operator is currently retired. Please renew the permit first.');
    return;
  }

  idPrintLoading.value = true;

  setTimeout(() => {
    try {
      const operator = selectedOperator.value!;

      // Add to print log
      addPrintLog('id_card', operator, undefined, bodyNumber.value, stickerNumber.value);

      const operatorName = operator.fullname;
      const driverName = operator.driver_fullname || 'N/A';
      const mtopNumber = operator.mtop_number;
      const todaName = operator.toda_name || 'N/A';
      const currentYear = new Date().getFullYear();
      const validUntil = `December 31, ${currentYear + 1}`;

      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>ID Card - ${driverName}</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
              @page {
                size: A4 portrait;
                margin: 0;
              }

              * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
              }

              body {
                font-family: 'Arial', 'Helvetica', sans-serif;
                background: white;
                color: black;
                width: 210mm;
                height: 297mm;
                display: flex;
                justify-content: center;
                align-items: center;
                overflow: hidden;
              }

              .id-card-container {
                width: 85.6mm;  /* Standard ID card width */
                height: 53.98mm; /* Standard ID card height */
                display: flex;
                position: relative;
                transform: scale(2.2); /* Scale to fit A4 */
                transform-origin: center;
                margin: 0 auto;
              }

              .id-front, .id-back {
                width: 85.6mm;
                height: 53.98mm;
                position: absolute;
                border: 1px solid #ccc;
                background: white;
              }

              .id-front {
                /* Front side styling */
                padding: 3mm;
                display: flex;
                flex-direction: column;
              }

              .id-back {
                /* Position back side next to front side */
                left: 90mm; /* Space between front and back */
              }

              /* Front Side Styles */
              .header {
                text-align: center;
                margin-bottom: 2mm;
              }

              .main-title {
                font-size: 8pt;
                font-weight: bold;
                color: #2c5aa0;
                margin-bottom: 1mm;
                line-height: 1;
              }

              .subtitle {
                font-size: 6pt;
                font-weight: bold;
                color: #2c5aa0;
                margin-bottom: 1mm;
                line-height: 1;
              }

              .id-type {
                font-size: 7pt;
                font-weight: bold;
                color: #333;
                margin-bottom: 2mm;
                text-decoration: underline;
              }

              .body-number-section {
                text-align: center;
                margin-bottom: 2mm;
              }

              .body-number-label {
                font-size: 6pt;
                font-weight: bold;
                color: #333;
                margin-bottom: 1mm;
              }

              .body-number {
                font-size: 14pt;
                font-weight: bold;
                color: #2c5aa0;
                background: #f0f4ff;
                padding: 1mm 3mm;
                border: 1px solid #2c5aa0;
                border-radius: 3px;
                display: inline-block;
              }

              .sticker-number {
                font-size: 6pt;
                font-weight: bold;
                color: #333;
                margin-top: 1mm;
              }

              .driver-info {
                margin-bottom: 2mm;
                padding: 2mm;
                background: #f8f9fa;
                border-radius: 2px;
                border-left: 2px solid #2c5aa0;
                flex-grow: 1;
              }

              .info-line {
                margin-bottom: 1mm;
              }

              .info-label {
                font-weight: bold;
                color: #2c5aa0;
                display: block;
                font-size: 5pt;
              }

              .info-value {
                font-weight: bold;
                color: #333;
                font-size: 6pt;
              }

              .signature-section {
                text-align: center;
                margin-top: 1mm;
              }

              .signature-line {
                width: 30mm;
                height: 0.5px;
                border-bottom: 1px solid #333;
                margin: 0 auto 0.5mm auto;
              }

              .signature-label {
                font-size: 5pt;
                color: #333;
                font-weight: bold;
                line-height: 1;
              }

              .validity {
                font-size: 4pt;
                text-align: center;
                margin-top: 1mm;
                color: #666;
                line-height: 1;
              }

              .change-notice {
                font-size: 3.5pt;
                text-align: center;
                margin-top: 1mm;
                color: #d63333;
                font-weight: bold;
                line-height: 1;
              }

              /* Back Side Styles */
              .id-back {
                padding: 2mm;
                display: flex;
                flex-direction: column;
              }

              .back-header {
                text-align: center;
                margin-bottom: 1mm;
              }

              .back-title {
                font-size: 7pt;
                font-weight: bold;
                color: #2c5aa0;
              }

              .back-content {
                flex-grow: 1;
                display: flex;
                flex-direction: column;
                gap: 1mm;
              }

              .back-section {
                padding: 1mm;
                background: white;
                border-radius: 1px;
                border-left: 1px solid #2c5aa0;
              }

              .back-label {
                font-weight: bold;
                font-size: 4pt;
                color: #2c5aa0;
                display: block;
              }

              .back-value {
                font-size: 5pt;
                font-weight: bold;
                color: #333;
              }

              .complaint-section {
                background: #fff3cd;
                border: 0.5px solid #ffc107;
                border-radius: 1px;
                padding: 1mm;
              }

              .complaint-title {
                font-size: 5pt;
                font-weight: bold;
                color: #856404;
                text-align: center;
                margin-bottom: 0.5mm;
              }

              .contact-info {
                font-size: 4pt;
                color: #333;
                line-height: 1;
                text-align: center;
              }

              .toda-section {
                background: #d1ecf1;
                border: 0.5px solid #0dcaf0;
                border-radius: 1px;
                padding: 1mm;
                text-align: center;
              }

              .toda-title {
                font-size: 4pt;
                font-weight: bold;
                color: #055160;
              }

              .toda-value {
                font-size: 5pt;
                font-weight: bold;
                color: #333;
              }

              .permit-number {
                background: #d4edda;
                border: 0.5px solid #198754;
                border-radius: 1px;
                padding: 1mm;
                text-align: center;
              }

              .permit-title {
                font-size: 4pt;
                font-weight: bold;
                color: #0f5132;
              }

              .permit-value {
                font-size: 5pt;
                font-weight: bold;
                color: #333;
              }

              .emergency-section {
                background: #f8d7da;
                border: 0.5px solid #dc3545;
                border-radius: 1px;
                padding: 1mm;
              }

              .emergency-title {
                font-size: 5pt;
                font-weight: bold;
                color: #721c24;
                text-align: center;
                margin-bottom: 0.5mm;
              }

              .emergency-numbers {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0.5mm;
              }

              .emergency-item {
                text-align: center;
                padding: 0.5mm;
                background: white;
                border-radius: 1px;
                font-size: 3.5pt;
              }

              .emergency-department {
                font-weight: bold;
                color: #333;
              }

              .emergency-number {
                font-weight: bold;
                color: #dc3545;
              }

              @media print {
                body {
                  margin: 0;
                  padding: 0;
                  width: 210mm;
                  height: 297mm;
                  display: flex;
                  justify-content: center;
                  align-items: center;
                }

                .id-card-container {
                  transform: scale(2.2);
                  margin: 0 auto;
                }

                .no-print {
                  display: none !important;
                }
              }

              .print-controls {
                text-align: center;
                margin: 10mm;
              }

              .print-controls button {
                padding: 4mm 8mm;
                margin: 2mm;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 11pt;
                font-weight: bold;
              }

              .print-btn {
                background: #2c5aa0;
                color: white;
              }

              .close-btn {
                background: #6b7280;
                color: white;
              }
            </style>
          </head>
          <body>
            <div class="id-card-container">
              <!-- Front Side -->
              <div class="id-front">
                <div class="header">
                  <div class="main-title">MARAMAG MOTORIZED TRICYCLE</div>
                  <div class="subtitle">FRANCHISING REGULATORY BOARD</div>
                  <div class="subtitle">( MMTFRB )</div>
                  <div class="id-type">DRIVER'S IDENTIFICATION</div>
                </div>

                <div class="body-number-section">
                  <div class="body-number-label">BODY NO.:</div>
                  <div class="body-number">${bodyNumber.value}</div>
                  <div class="sticker-number">STICKER NO.: ${stickerNumber.value}</div>
                </div>

                <div class="driver-info">
                  <div class="info-line">
                    <span class="info-label">NAME OF DRIVER</span>
                    <span class="info-value">${driverName}</span>
                  </div>
                  <div class="info-line">
                    <span class="info-label">NAME OF OPERATOR</span>
                    <span class="info-value">${operatorName}</span>
                  </div>
                </div>

                <div class="signature-section">
                  <div class="signature-line"></div>
                  <div class="signature-label">ATTY JOSE JUDE L.P. DOROMAL</div>
                  <div class="signature-label">MUNICIPAL MAYOR / CHAIRMAN MMTFRB</div>
                </div>

                <div class="validity">
                  THIS ID IS VALID UNTIL ${validUntil}
                </div>

                <div class="change-notice">
                  IN CASE OF CHANGE OF DRIVER, PLEASE SURRENDER THIS ID TO THE BUSINESS PERMIT & LICENSING OFFICE (BPLO)
                </div>
              </div>

              <!-- Back Side - Positioned next to front side -->
              <div class="id-back">
                <div class="back-header">
                  <div class="back-title">DRIVER INFORMATION</div>
                </div>

                <div class="back-content">
                  <div class="back-section">
                    <span class="back-label">NAME OF DRIVER:</span>
                    <span class="back-value">${driverName}</span>
                  </div>

                  <div class="back-section">
                    <span class="back-label">BODY NO.:</span>
                    <span class="back-value">${bodyNumber.value}</span>
                  </div>

                  <div class="complaint-section">
                    <div class="complaint-title">IN CASE OF COMPLAINT:</div>
                    <div class="contact-info">
                      📍 09752478085 | 088 828 1811<br>
                      📎 bplo_maramag@gmail.com
                    </div>
                  </div>

                  <div class="toda-section">
                    <div class="toda-title">TODA:</div>
                    <div class="toda-value">${todaName}</div>
                  </div>

                  <div class="permit-number">
                    <div class="permit-title">PERMIT NO.:</div>
                    <div class="permit-value">${mtopNumber}-${new Date().getFullYear()}</div>
                  </div>

                  <div class="emergency-section">
                    <div class="emergency-title">EMERGENCY HOTLINE NUMBERS</div>
                    <div class="emergency-numbers">
                      <div class="emergency-item">
                        <div class="emergency-department">MDRRMO/RESCUE</div>
                        <div class="emergency-number">0966-768-2770</div>
                      </div>
                      <div class="emergency-item">
                        <div class="emergency-department">MHO - MARAMAG</div>
                        <div class="emergency-number">0966-042-2004</div>
                      </div>
                      <div class="emergency-item">
                        <div class="emergency-department">BFP - MARAMAG</div>
                        <div class="emergency-number">0917-516-3291</div>
                      </div>
                      <div class="emergency-item">
                        <div class="emergency-department">PNP - MARAMAG</div>
                        <div class="emergency-number">0953-125-6859</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Print Controls -->
            <div class="no-print print-controls">
              <button onclick="window.print()" class="print-btn">🖨️ Print ID Card</button>
              <button onclick="window.close()" class="close-btn">❌ Close Window</button>
              <p style="font-size: 10pt; color: #666; margin-top: 5mm;">
                Print Size: A4 Portrait | Front and Back on One Page | Cut along the edges
              </p>
            </div>

            <script>
              window.onload = function() {
                setTimeout(() => {
                  window.print();
                }, 500);

                window.onafterprint = function() {
                  setTimeout(() => {
                    window.close();
                  }, 100);
                };
              };
            <\/script>
          </body>
          </html>
        `);
        printWindow.document.close();
      }
    } catch (err) {
      console.error('Error printing ID card:', err);
      alert('Error generating ID card preview. Please try again.');
    } finally {
      idPrintLoading.value = false;
      closePrintModal();
    }
  }, 500);
};

// Report Printing Functions
const printReport = () => {
  reportLoading.value = true;

  setTimeout(() => {
    try {
      const stats = getPrintStats.value;

      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>Print Report - ${stats.date_range}</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
              @page {
                size: A4;
                margin: 15mm;
              }

              * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
              }

              body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
                background: white;
                color: black;
                font-size: 12px;
                line-height: 1.4;
              }

              .report-container {
                max-width: 100%;
                margin: 0 auto;
              }

              .report-header {
                text-align: center;
                margin-bottom: 8mm;
                border-bottom: 2px solid #1e40af;
                padding-bottom: 4mm;
              }

              .report-header h1 {
                margin: 0 0 2mm 0;
                font-size: 24px;
                color: #1e40af;
                font-weight: bold;
                text-transform: uppercase;
              }

              .report-header h2 {
                margin: 0 0 1mm 0;
                font-size: 18px;
                color: #374151;
                font-weight: 600;
              }

              .report-header h3 {
                margin: 0;
                font-size: 14px;
                color: #6b7280;
                font-weight: normal;
              }

              .stats-section {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 4mm;
                margin-bottom: 6mm;
                padding: 3mm;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
              }

              .stat-item {
                text-align: center;
                padding: 3mm;
                background: white;
                border-radius: 4px;
                border: 1px solid #e2e8f0;
              }

              .stat-value {
                font-size: 24px;
                font-weight: bold;
                color: #1e40af;
                margin-bottom: 1mm;
              }

              .stat-label {
                font-size: 12px;
                color: #64748b;
                font-weight: 600;
                text-transform: uppercase;
              }

              .logs-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 6mm;
              }

              .logs-table th {
                background: #1e40af;
                color: white;
                padding: 2mm 1mm;
                text-align: left;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
                border: 1px solid #1e3a8a;
              }

              .logs-table td {
                padding: 1.5mm 1mm;
                border: 1px solid #e2e8f0;
                font-size: 10px;
                vertical-align: top;
              }

              .logs-table tr:nth-child(even) {
                background: #f8fafc;
              }

              .type-badge {
                padding: 1mm 2mm;
                border-radius: 12px;
                font-size: 9px;
                font-weight: bold;
                display: inline-block;
                text-transform: uppercase;
              }

              .type-permit {
                background: #dbeafe;
                color: #1e40af;
                border: 1px solid #1e40af;
              }

              .type-id_card {
                background: #dcfce7;
                color: #166534;
                border: 1px solid #166534;
              }

              .report-footer {
                text-align: center;
                margin-top: 8mm;
                padding-top: 3mm;
                border-top: 1px solid #e2e8f0;
                font-size: 10px;
                color: #64748b;
              }

              @media print {
                body {
                  margin: 0;
                  padding: 0;
                  background: white !important;
                  -webkit-print-color-adjust: exact;
                  print-color-adjust: exact;
                }

                .no-print {
                  display: none !important;
                }
              }
            </style>
          </head>
          <body>
            <div class="report-container">
              <div class="report-header">
                <h1>MUNICIPALITY OF MARAMAG</h1>
                <h2>PRINTING ACTIVITY REPORT</h2>
                <h3>MARAMAG MOTORIZED TRICYCLE FRANCHISING REGULATORY BOARD</h3>
                <p style="margin-top: 2mm; font-size: 12px; color: #6b7280;">
                  Date Range: ${stats.date_range}
                </p>
              </div>

              <div class="stats-section">
                <div class="stat-item">
                  <div class="stat-value">${stats.total}</div>
                  <div class="stat-label">Total Prints</div>
                </div>
                <div class="stat-item">
                  <div class="stat-value">${stats.permits}</div>
                  <div class="stat-label">Permits Printed</div>
                </div>
                <div class="stat-item">
                  <div class="stat-value">${stats.id_cards}</div>
                  <div class="stat-label">ID Cards Printed</div>
                </div>
              </div>

              <table class="logs-table">
                <thead>
                  <tr>
                    <th style="width: 12%">Date & Time</th>
                    <th style="width: 8%">Type</th>
                    <th style="width: 15%">Operator</th>
                    <th style="width: 15%">Driver</th>
                    <th style="width: 10%">MTOP No.</th>
                    <th style="width: 10%">OR Number</th>
                    <th style="width: 8%">Body No.</th>
                    <th style="width: 8%">Sticker No.</th>
                    <th style="width: 8%">Quarter</th>
                    <th style="width: 6%">Printed By</th>
                  </tr>
                </thead>
                <tbody>
                  ${filteredLogs.value.map(log => `
                    <tr>
                      <td>${log.printed_at}</td>
                      <td>
                        <span class="type-badge ${log.type === 'permit' ? 'type-permit' : 'type-id_card'}">
                          ${log.type === 'permit' ? 'Permit' : 'ID Card'}
                        </span>
                      </td>
                      <td>${log.operator_name}</td>
                      <td>${log.driver_name}</td>
                      <td>${log.mtop_number}</td>
                      <td>${log.or_number || 'N/A'}</td>
                      <td>${log.body_number || 'N/A'}</td>
                      <td>${log.sticker_number || 'N/A'}</td>
                      <td>${log.quarter}</td>
                      <td>${log.printed_by}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>

              <div class="report-footer">
                <p><strong>Generated by:</strong> MARAMAG MMTFRB System</p>
                <p>MARAMAG MUNICIPALITY • BUSINESS PERMIT & LICENSING OFFICE • www.maramag.gov.ph</p>
                <p style="margin-top: 2mm;">Generated on: ${new Date().toLocaleString('en-PH')}</p>
              </div>
            </div>

            <script>
              window.onload = function() {
                setTimeout(() => {
                  window.print();
                }, 500);

                window.onafterprint = function() {
                  setTimeout(() => {
                    window.close();
                  }, 100);
                };
              };
            <\/script>
          </body>
          </html>
        `);
        printWindow.document.close();
      }
    } catch (err) {
      console.error('Error printing report:', err);
      alert('Error generating report preview. Please try again.');
    } finally {
      reportLoading.value = false;
    }
  }, 500);
};

const printPermitReport = () => {
  reportLoading.value = true;

  setTimeout(() => {
    try {
      const stats = getPermitStats.value;

      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>Permit Print Report - ${stats.date_range}</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
              @page {
                size: A4;
                margin: 15mm;
              }

              * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
              }

              body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
                background: white;
                color: black;
                font-size: 12px;
                line-height: 1.4;
              }

              .report-container {
                max-width: 100%;
                margin: 0 auto;
              }

              .report-header {
                text-align: center;
                margin-bottom: 8mm;
                border-bottom: 2px solid #1e40af;
                padding-bottom: 4mm;
              }

              .report-header h1 {
                margin: 0 0 2mm 0;
                font-size: 24px;
                color: #1e40af;
                font-weight: bold;
                text-transform: uppercase;
              }

              .report-header h2 {
                margin: 0 0 1mm 0;
                font-size: 18px;
                color: #374151;
                font-weight: 600;
              }

              .report-header h3 {
                margin: 0;
                font-size: 14px;
                color: #6b7280;
                font-weight: normal;
              }

              .stats-section {
                display: grid;
                grid-template-columns: 1fr;
                gap: 4mm;
                margin-bottom: 6mm;
                padding: 3mm;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 6px;
              }

              .stat-item {
                text-align: center;
                padding: 3mm;
                background: white;
                border-radius: 4px;
                border: 1px solid #e2e8f0;
              }

              .stat-value {
                font-size: 24px;
                font-weight: bold;
                color: #1e40af;
                margin-bottom: 1mm;
              }

              .stat-label {
                font-size: 12px;
                color: #64748b;
                font-weight: 600;
                text-transform: uppercase;
              }

              .logs-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 6mm;
              }

              .logs-table th {
                background: #1e40af;
                color: white;
                padding: 2mm 1mm;
                text-align: left;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
                border: 1px solid #1e3a8a;
              }

              .logs-table td {
                padding: 1.5mm 1mm;
                border: 1px solid #e2e8f0;
                font-size: 10px;
                vertical-align: top;
              }

              .logs-table tr:nth-child(even) {
                background: #f8fafc;
              }

              .report-footer {
                text-align: center;
                margin-top: 8mm;
                padding-top: 3mm;
                border-top: 1px solid #e2e8f0;
                font-size: 10px;
                color: #64748b;
              }

              @media print {
                body {
                  margin: 0;
                  padding: 0;
                  background: white !important;
                  -webkit-print-color-adjust: exact;
                  print-color-adjust: exact;
                }

                .no-print {
                  display: none !important;
                }
              }
            </style>
          </head>
          <body>
            <div class="report-container">
              <div class="report-header">
                <h1>MUNICIPALITY OF MARAMAG</h1>
                <h2>PERMIT PRINTING REPORT</h2>
                <h3>MARAMAG MOTORIZED TRICYCLE FRANCHISING REGULATORY BOARD</h3>
                <p style="margin-top: 2mm; font-size: 12px; color: #6b7280;">
                  Date Range: ${stats.date_range}
                </p>
              </div>

              <div class="stats-section">
                <div class="stat-item">
                  <div class="stat-value">${stats.total}</div>
                  <div class="stat-label">Permits Printed</div>
                </div>
              </div>

              <table class="logs-table">
                <thead>
                  <tr>
                    <th style="width: 15%">Date & Time</th>
                    <th style="width: 20%">Operator</th>
                    <th style="width: 20%">Driver</th>
                    <th style="width: 15%">MTOP No.</th>
                    <th style="width: 15%">OR Number</th>
                    <th style="width: 15%">Quarter</th>
                  </tr>
                </thead>
                <tbody>
                  ${filteredPermitLogs.value.map(log => `
                    <tr>
                      <td>${log.printed_at}</td>
                      <td>${log.operator_name}</td>
                      <td>${log.driver_name}</td>
                      <td>${log.mtop_number}</td>
                      <td>${log.or_number || 'N/A'}</td>
                      <td>${log.quarter}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>

              <div class="report-footer">
                <p><strong>Generated by:</strong> MARAMAG MMTFRB System</p>
                <p>MARAMAG MUNICIPALITY • BUSINESS PERMIT & LICENSING OFFICE • www.maramag.gov.ph</p>
                <p style="margin-top: 2mm;">Generated on: ${new Date().toLocaleString('en-PH')}</p>
              </div>
            </div>

            <script>
              window.onload = function() {
                setTimeout(() => {
                  window.print();
                }, 500);

                window.onafterprint = function() {
                  setTimeout(() => {
                    window.close();
                  }, 100);
                };
              };
            <\/script>
          </body>
          </html>
        `);
        printWindow.document.close();
      }
    } catch (err) {
      console.error('Error printing permit report:', err);
      alert('Error generating permit report preview. Please try again.');
    } finally {
      reportLoading.value = false;
    }
  }, 500);
};

const printIdReport = () => {
  reportLoading.value = true;

  setTimeout(() => {
    try {
      const stats = getIdStats.value;

      const printWindow = window.open('', '_blank');
      if (printWindow) {
        printWindow.document.write(`
          <!DOCTYPE html>
          <html>
          <head>
            <title>ID Card Print Report - ${stats.date_range}</title>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
              @page {
                size: A4;
                margin: 15mm;
              }

              * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
              }

              body {
                font-family: 'Arial', sans-serif;
                margin: 0;
                padding: 0;
                background: white;
                color: black;
                font-size: 12px;
                line-height: 1.4;
              }

              .report-container {
                max-width: 100%;
                margin: 0 auto;
              }

              .report-header {
                text-align: center;
                margin-bottom: 8mm;
                border-bottom: 2px solid #166534;
                padding-bottom: 4mm;
              }

              .report-header h1 {
                margin: 0 0 2mm 0;
                font-size: 24px;
                color: #166534;
                font-weight: bold;
                text-transform: uppercase;
              }

              .report-header h2 {
                margin: 0 0 1mm 0;
                font-size: 18px;
                color: #374151;
                font-weight: 600;
              }

              .report-header h3 {
                margin: 0;
                font-size: 14px;
                color: #6b7280;
                font-weight: normal;
              }

              .stats-section {
                display: grid;
                grid-template-columns: 1fr;
                gap: 4mm;
                margin-bottom: 6mm;
                padding: 3mm;
                background: #f0fdf4;
                border: 1px solid #dcfce7;
                border-radius: 6px;
              }

              .stat-item {
                text-align: center;
                padding: 3mm;
                background: white;
                border-radius: 4px;
                border: 1px solid #dcfce7;
              }

              .stat-value {
                font-size: 24px;
                font-weight: bold;
                color: #166534;
                margin-bottom: 1mm;
              }

              .stat-label {
                font-size: 12px;
                color: #64748b;
                font-weight: 600;
                text-transform: uppercase;
              }

              .logs-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 6mm;
              }

              .logs-table th {
                background: #166534;
                color: white;
                padding: 2mm 1mm;
                text-align: left;
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
                border: 1px solid #14532d;
              }

              .logs-table td {
                padding: 1.5mm 1mm;
                border: 1px solid #e2e8f0;
                font-size: 10px;
                vertical-align: top;
              }

              .logs-table tr:nth-child(even) {
                background: #f0fdf4;
              }

              .report-footer {
                text-align: center;
                margin-top: 8mm;
                padding-top: 3mm;
                border-top: 1px solid #e2e8f0;
                font-size: 10px;
                color: #64748b;
              }

              @media print {
                body {
                  margin: 0;
                  padding: 0;
                  background: white !important;
                  -webkit-print-color-adjust: exact;
                  print-color-adjust: exact;
                }

                .no-print {
                  display: none !important;
                }
              }
            </style>
          </head>
          <body>
            <div class="report-container">
              <div class="report-header">
                <h1>MUNICIPALITY OF MARAMAG</h1>
                <h2>ID CARD PRINTING REPORT</h2>
                <h3>MARAMAG MOTORIZED TRICYCLE FRANCHISING REGULATORY BOARD</h3>
                <p style="margin-top: 2mm; font-size: 12px; color: #6b7280;">
                  Date Range: ${stats.date_range}
                </p>
              </div>

              <div class="stats-section">
                <div class="stat-item">
                  <div class="stat-value">${stats.total}</div>
                  <div class="stat-label">ID Cards Printed</div>
                </div>
              </div>

              <table class="logs-table">
                <thead>
                  <tr>
                    <th style="width: 15%">Date & Time</th>
                    <th style="width: 20%">Operator</th>
                    <th style="width: 20%">Driver</th>
                    <th style="width: 15%">MTOP No.</th>
                    <th style="width: 15%">Body No.</th>
                    <th style="width: 15%">Sticker No.</th>
                  </tr>
                </thead>
                <tbody>
                  ${filteredIdLogs.value.map(log => `
                    <tr>
                      <td>${log.printed_at}</td>
                      <td>${log.operator_name}</td>
                      <td>${log.driver_name}</td>
                      <td>${log.mtop_number}</td>
                      <td>${log.body_number || 'N/A'}</td>
                      <td>${log.sticker_number || 'N/A'}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>

              <div class="report-footer">
                <p><strong>Generated by:</strong> MARAMAG MMTFRB System</p>
                <p>MARAMAG MUNICIPALITY • BUSINESS PERMIT & LICENSING OFFICE • www.maramag.gov.ph</p>
                <p style="margin-top: 2mm;">Generated on: ${new Date().toLocaleString('en-PH')}</p>
              </div>
            </div>

            <script>
              window.onload = function() {
                setTimeout(() => {
                  window.print();
                }, 500);

                window.onafterprint = function() {
                  setTimeout(() => {
                    window.close();
                  }, 100);
                };
              };
            <\/script>
          </body>
          </html>
        `);
        printWindow.document.close();
      }
    } catch (err) {
      console.error('Error printing ID report:', err);
      alert('Error generating ID report preview. Please try again.');
    } finally {
      reportLoading.value = false;
    }
  }, 500);
};

const exportReport = () => {
  const stats = getPrintStats.value;
  const csvContent = [
    ['Date & Time', 'Type', 'Operator', 'Driver', 'MTOP Number', 'OR Number', 'Body Number', 'Sticker Number', 'Quarter', 'Printed By'],
    ...filteredLogs.value.map(log => [
      log.printed_at,
      log.type === 'permit' ? 'Permit' : 'ID Card',
      log.operator_name,
      log.driver_name,
      log.mtop_number,
      log.or_number || 'N/A',
      log.body_number || 'N/A',
      log.sticker_number || 'N/A',
      log.quarter,
      log.printed_by
    ])
  ].map(row => row.map(field => `"${field}"`).join(',')).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `maramag_print_report_${reportDateRange.value.start}_to_${reportDateRange.value.end}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const exportPermitReport = () => {
  const stats = getPermitStats.value;
  const csvContent = [
    ['Date & Time', 'Operator', 'Driver', 'MTOP Number', 'OR Number', 'Quarter', 'Printed By'],
    ...filteredPermitLogs.value.map(log => [
      log.printed_at,
      log.operator_name,
      log.driver_name,
      log.mtop_number,
      log.or_number || 'N/A',
      log.quarter,
      log.printed_by
    ])
  ].map(row => row.map(field => `"${field}"`).join(',')).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `maramag_permit_report_${reportDateRange.value.start}_to_${reportDateRange.value.end}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const exportIdReport = () => {
  const stats = getIdStats.value;
  const csvContent = [
    ['Date & Time', 'Operator', 'Driver', 'MTOP Number', 'Body Number', 'Sticker Number', 'Quarter', 'Printed By'],
    ...filteredIdLogs.value.map(log => [
      log.printed_at,
      log.operator_name,
      log.driver_name,
      log.mtop_number,
      log.body_number || 'N/A',
      log.sticker_number || 'N/A',
      log.quarter,
      log.printed_by
    ])
  ].map(row => row.map(field => `"${field}"`).join(',')).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  const url = URL.createObjectURL(blob);
  link.setAttribute('href', url);
  link.setAttribute('download', `maramag_id_report_${reportDateRange.value.start}_to_${reportDateRange.value.end}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

onMounted(async () => {
  await fetchStats();
  loadPrintLogs();

  if (operators.value.length === 0) {
    console.log('No operators loaded, trying direct fetch...');
    await fetchOperatorsDirectly();
  }
});
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 p-4">
      <!-- Error Message -->
      <div v-if="error" class="rounded-2xl border border-red-600 bg-red-500 p-4">
        <p class="text-white">{{ error }}</p>
      </div>

      <!-- Header -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-white">Operator Management Dashboard</h1>
          <p class="text-gray-400">Manage tricycle operators, print permits and ID cards</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-2">
          <button
            @click="openPermitReportModal"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Permit Reports
          </button>
          <button
            @click="openIdReportModal"
            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            ID Reports
          </button>
          <button
            @click="openReportModal"
            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            All Reports
          </button>
        </div>
      </div>

      <!-- Operator Management Metrics -->
      <div class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl sm:rounded-2xl border border-blue-600 bg-gray-800 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-300">Total Operators</p>
          <h4 class="mt-2 sm:mt-3 text-xl sm:text-2xl font-bold text-white">{{ stats.total_operators }}</h4>
          <div class="mt-1 sm:mt-2 text-xs text-gray-400">
            <span class="text-green-500">{{ stats.new_permits }} New</span> •
            <span class="text-blue-500">{{ stats.renew_permits }} Renewed</span> •
            <span class="text-red-500">{{ stats.retire_permits }} Retired</span>
          </div>
        </div>

        <div class="rounded-xl sm:rounded-2xl border border-green-600 bg-gray-800 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-300">Total Drivers</p>
          <h4 class="mt-2 sm:mt-3 text-xl sm:text-2xl font-bold text-white">{{ stats.total_drivers }}</h4>
          <div class="mt-1 sm:mt-2 text-xs text-gray-400">
            Licensed tricycle drivers
          </div>
        </div>

        <div class="rounded-xl sm:rounded-2xl border border-yellow-600 bg-gray-800 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-300">Total Motorcycles</p>
          <h4 class="mt-2 sm:mt-3 text-xl sm:text-2xl font-bold text-white">{{ stats.total_motorcycles }}</h4>
          <div class="mt-1 sm:mt-2 text-xs text-gray-400">
            Registered tricycle units
          </div>
        </div>

        <div class="rounded-xl sm:rounded-2xl border border-purple-600 bg-gray-800 p-4 sm:p-5">
          <p class="text-xs sm:text-sm text-gray-300">Total TODA</p>
          <h4 class="mt-2 sm:mt-3 text-xl sm:text-2xl font-bold text-white">{{ stats.total_toda }}</h4>
          <div class="mt-1 sm:mt-2 text-xs text-gray-400">
            Tricycle Operators & Drivers Associations
          </div>
        </div>
      </div>

      <!-- Print Activity Summary -->
      <div class="rounded-xl sm:rounded-2xl border border-gray-700 bg-gray-800 p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3 sm:mb-4">Recent Print Activity</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
          <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-center">
            <div class="text-blue-400 text-xl sm:text-2xl font-bold">{{ printLogs.filter(log => log.type === 'permit').length }}</div>
            <div class="text-gray-300 text-xs sm:text-sm">Permits Printed</div>
          </div>
          <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-center">
            <div class="text-green-400 text-xl sm:text-2xl font-bold">{{ printLogs.filter(log => log.type === 'id_card').length }}</div>
            <div class="text-gray-300 text-xs sm:text-sm">ID Cards Printed</div>
          </div>
          <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-center">
            <div class="text-purple-400 text-xl sm:text-2xl font-bold">{{ printLogs.length }}</div>
            <div class="text-gray-300 text-xs sm:text-sm">Total Prints</div>
          </div>
        </div>
      </div>

      <!-- Permit Status Summary -->
      <div class="rounded-xl sm:rounded-2xl border border-gray-700 bg-gray-800 p-4 sm:p-6">
        <h3 class="text-base sm:text-lg font-semibold text-white mb-3 sm:mb-4">Permit Status Overview</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
          <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-center">
            <div class="text-green-500 text-xl sm:text-2xl font-bold">{{ stats.new_permits }}</div>
            <div class="text-gray-300 text-xs sm:text-sm">New Permits</div>
          </div>
          <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-center">
            <div class="text-blue-500 text-xl sm:text-2xl font-bold">{{ stats.renew_permits }}</div>
            <div class="text-gray-300 text-xs sm:text-sm">Renewed Permits</div>
          </div>
          <div class="bg-gray-700 rounded-lg p-3 sm:p-4 text-center">
            <div class="text-red-500 text-xl sm:text-2xl font-bold">{{ stats.retire_permits }}</div>
            <div class="text-gray-300 text-xs sm:text-sm">Retired Permits</div>
          </div>
        </div>
      </div>

      <!-- Operators Data Grid -->
      <div class="rounded-xl sm:rounded-2xl border border-gray-700 bg-gray-800 p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-0 mb-4 sm:mb-6">
          <h3 class="text-base sm:text-lg font-semibold text-white">Operator Management</h3>
          <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <input
              v-model="searchTerm"
              type="text"
              placeholder="Search operators..."
              class="rounded-md p-2 bg-gray-700 text-white placeholder-gray-400 border border-gray-600 focus:border-blue-500 focus:outline-none w-full sm:w-64 text-sm sm:text-base"
            />
            <span class="text-gray-300 flex items-center px-3 bg-gray-700 rounded-md text-sm h-10">
              Page {{ currentPage }} / {{ totalPages }}
            </span>
          </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-6 sm:py-8">
          <div class="spinner-border inline-block h-6 w-6 sm:h-8 sm:w-8 animate-spin rounded-full border-4 border-t-blue-500 border-gray-700"></div>
          <span class="ml-2 text-gray-300 text-sm sm:text-base">Loading operators...</span>
        </div>

        <div v-else-if="operators.length === 0" class="text-center py-6 sm:py-8">
          <p class="text-gray-400 text-sm sm:text-base">No operators found.</p>
          <button
            @click="fetchOperatorsDirectly"
            class="mt-2 px-3 py-2 sm:px-4 sm:py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm sm:text-base"
          >
            Retry Loading
          </button>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-white border-collapse text-sm sm:text-base">
            <thead>
              <tr class="border-b border-gray-600">
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm">Operator</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm hidden sm:table-cell">Contact</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm">Driver</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm hidden md:table-cell">Motorcycle</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm">TODA</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm">MTOP No.</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm">Status</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm hidden lg:table-cell">Date Registered</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm">Quarter</th>
                <th class="p-2 sm:p-3 font-semibold text-xs sm:text-sm">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(operator, idx) in paginatedData"
                :key="operator.id"
                :class="['border-b border-gray-700 transition-colors',
                        operator.permit_status === 'retire' ? 'bg-red-900 bg-opacity-20 hover:bg-red-800 hover:bg-opacity-30' : 'hover:bg-gray-700']"
              >
                <td class="p-2 sm:p-3">
                  <div class="font-medium text-xs sm:text-sm">{{ operator.fullname }}</div>
                  <div class="text-xs text-gray-400 sm:hidden">{{ operator.contact_number }}</div>
                  <div class="text-xs text-gray-400">{{ operator.email_address }}</div>
                  <!-- Retirement warning for mobile -->
                  <div v-if="operator.permit_status === 'retire'" class="sm:hidden mt-1">
                    <span class="px-2 py-1 bg-red-500 text-white rounded-full text-xs">Retired - Printing Disabled</span>
                  </div>
                </td>
                <td class="p-2 sm:p-3 hidden sm:table-cell">{{ operator.contact_number }}</td>
                <td class="p-2 sm:p-3">
                  <span class="text-xs sm:text-sm">{{ displayValue(operator.driver_fullname) }}</span>
                </td>
                <td class="p-2 sm:p-3 hidden md:table-cell">
                  <span class="text-xs sm:text-sm">{{ displayValue(operator.plate_number) }}</span>
                </td>
                <td class="p-2 sm:p-3">
                  <span class="text-xs sm:text-sm">{{ displayValue(operator.toda_name) }}</span>
                </td>
                <td class="p-2 sm:p-3 font-mono text-xs sm:text-sm">{{ operator.mtop_number }}</td>
                <td class="p-2 sm:p-3">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(operator.permit_status)]">
                    {{ getStatusText(operator.permit_status) }}
                  </span>
                </td>
                <td class="p-2 sm:p-3 hidden lg:table-cell text-xs sm:text-sm">
                  {{ new Date(operator.date_registered).toLocaleDateString() }}
                </td>
                <td class="p-2 sm:p-3">
                  <span :class="['px-2 py-1 rounded-full text-xs font-medium border', getQuarterBadgeColor(getQuarterFromDate(operator.date_last_paid || operator.date_registered))]">
                    {{ getQuarterFromDate(operator.date_last_paid || operator.date_registered) }}
                  </span>
                </td>
                <td class="p-2 sm:p-3">
                  <div class="flex flex-col sm:flex-row gap-1 sm:gap-2">
                    <button
                      @click="openPrintModal(operator)"
                      :disabled="operator.permit_status === 'retire'"
                      :class="[
                        'px-2 py-1 rounded text-xs transition-colors',
                        operator.permit_status === 'retire'
                          ? 'bg-gray-500 text-gray-300 cursor-not-allowed'
                          : 'bg-blue-600 hover:bg-blue-700 text-white'
                      ]"
                      :title="operator.permit_status === 'retire' ? 'Renew permit to enable printing' : 'Print Permit'"
                    >
                      {{ operator.permit_status === 'retire' ? 'Disabled' : 'Print Permit' }}
                    </button>
                    <button
                      @click="openIdPrintModal(operator)"
                      :disabled="operator.permit_status === 'retire'"
                      :class="[
                        'px-2 py-1 rounded text-xs transition-colors',
                        operator.permit_status === 'retire'
                          ? 'bg-gray-500 text-gray-300 cursor-not-allowed'
                          : 'bg-green-600 hover:bg-green-700 text-white'
                      ]"
                      :title="operator.permit_status === 'retire' ? 'Renew permit to enable printing' : 'Print ID Card'"
                    >
                      {{ operator.permit_status === 'retire' ? 'Disabled' : 'Print ID' }}
                    </button>
                  </div>
                  <!-- Retirement warning for desktop -->
                  <div v-if="operator.permit_status === 'retire'" class="hidden sm:block mt-1">
                    <span class="text-red-400 text-xs">Renew to enable printing</span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="filteredData.length > 0" class="flex flex-col sm:flex-row justify-between items-center gap-3 mt-4">
            <button
              class="px-3 py-2 sm:px-4 sm:py-2 bg-gray-700 rounded hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm w-full sm:w-auto"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              ← Previous
            </button>
            <span class="text-gray-300 text-sm text-center">
              Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, filteredData.length) }} of {{ filteredData.length }} operators
            </span>
            <button
              class="px-3 py-2 sm:px-4 sm:py-2 bg-gray-700 rounded hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm w-full sm:w-auto"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Next →
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Keep all your existing modals here -->
    <!-- Print Permit Modal -->
    <div v-if="showPrintModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Print MARAMAG MMTFRB Permit</h3>

          <!-- Retirement Warning in Modal -->
          <div v-if="selectedOperator?.permit_status === 'retire'" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <strong class="font-bold">Permit Retired - Printing Disabled</strong>
            </div>
            <p class="text-sm mt-1">This operator's permit is currently retired. Please renew the permit to enable printing.</p>
          </div>

          <p class="text-gray-600 mb-4" :class="{'opacity-50': selectedOperator?.permit_status === 'retire'}">
            Print official permit for <strong>{{ selectedOperator?.fullname }}</strong>?
            <br><span class="text-sm text-blue-600">Print Size: A4 Full Page | Official Permit Document</span>
          </p>

          <div class="space-y-4 mb-4" :class="{'opacity-50': selectedOperator?.permit_status === 'retire'}">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                OR Number *
              </label>
              <input
                v-model="orNumber"
                type="text"
                placeholder="Enter Official Receipt Number"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="selectedOperator?.permit_status === 'retire'"
                required
              />
              <p class="text-xs text-gray-500 mt-1">
                Official Receipt Number is required for permit printing
              </p>
            </div>
          </div>

          <div class="bg-gray-50 p-3 rounded-md mb-4" :class="{'opacity-50': selectedOperator?.permit_status === 'retire'}">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Permit Information:</h4>
            <div class="text-xs text-gray-600 space-y-1">
              <div><strong>Operator:</strong> {{ selectedOperator?.fullname }}</div>
              <div><strong>Contact:</strong> {{ selectedOperator?.contact_number }}</div>
              <div><strong>Driver:</strong> {{ selectedOperator?.driver_fullname || 'N/A' }}</div>
              <div><strong>TODA:</strong> {{ selectedOperator?.toda_name || 'N/A' }}</div>
              <div><strong>MTOP No:</strong> {{ selectedOperator?.mtop_number }}</div>
              <div><strong>Status:</strong>
                <span :class="['ml-1 px-2 py-1 rounded-full text-xs font-medium', getStatusColor(selectedOperator?.permit_status || '')]">
                  {{ getStatusText(selectedOperator?.permit_status || '') }}
                </span>
              </div>
              <div><strong>Current Quarter:</strong> {{ selectedOperator ? getQuarterFromDate(selectedOperator.date_last_paid || selectedOperator.date_registered) : 'N/A' }}</div>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button
              @click="closePrintModal"
              class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition-colors"
              :disabled="printLoading"
            >
              Cancel
            </button>
            <button
              @click="printPermit"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-2"
              :disabled="printLoading || !orNumber.trim() || selectedOperator?.permit_status === 'retire'"
              :class="{'opacity-50 cursor-not-allowed': selectedOperator?.permit_status === 'retire'}"
            >
              <span v-if="printLoading" class="spinner-border inline-block h-4 w-4 animate-spin rounded-full border-2 border-t-white border-blue-200"></span>
              {{ selectedOperator?.permit_status === 'retire' ? 'Printing Disabled' : (printLoading ? 'Printing...' : 'Print Permit') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Print ID Modal -->
    <div v-if="showIdPrintModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full">
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Print MARAMAG MMTFRB ID Card</h3>

          <!-- Retirement Warning in Modal -->
          <div v-if="selectedOperator?.permit_status === 'retire'" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <strong class="font-bold">Permit Retired - Printing Disabled</strong>
            </div>
            <p class="text-sm mt-1">This operator's permit is currently retired. Please renew the permit to enable printing.</p>
          </div>

          <p class="text-gray-600 mb-4" :class="{'opacity-50': selectedOperator?.permit_status === 'retire'}">
            Print official ID card for <strong>{{ selectedOperator?.driver_fullname || 'N/A' }}</strong>?
            <br><span class="text-sm text-blue-600">Print Size: A4 Portrait (Front and Back on One Page)</span>
          </p>

          <div class="space-y-4 mb-4" :class="{'opacity-50': selectedOperator?.permit_status === 'retire'}">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Body Number *
              </label>
              <input
                v-model="bodyNumber"
                type="text"
                placeholder="Enter body number (e.g., 0063)"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                :disabled="selectedOperator?.permit_status === 'retire'"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Sticker Number *
              </label>
              <input
                v-model="stickerNumber"
                type="text"
                placeholder="Enter sticker number"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                :disabled="selectedOperator?.permit_status === 'retire'"
                required
              />
            </div>
          </div>

          <div class="bg-gray-50 p-3 rounded-md mb-4" :class="{'opacity-50': selectedOperator?.permit_status === 'retire'}">
            <h4 class="text-sm font-medium text-gray-700 mb-2">ID Card Information:</h4>
            <div class="text-xs text-gray-600 space-y-1">
              <div><strong>Driver:</strong> {{ selectedOperator?.driver_fullname || 'N/A' }}</div>
              <div><strong>Operator:</strong> {{ selectedOperator?.fullname }}</div>
              <div><strong>TODA:</strong> {{ selectedOperator?.toda_name || 'N/A' }}</div>
              <div><strong>MTOP No:</strong> {{ selectedOperator?.mtop_number }}</div>
              <div><strong>Status:</strong>
                <span :class="['ml-1 px-2 py-1 rounded-full text-xs font-medium', getStatusColor(selectedOperator?.permit_status || '')]">
                  {{ getStatusText(selectedOperator?.permit_status || '') }}
                </span>
              </div>
              <div><strong>Current Quarter:</strong> {{ selectedOperator ? getQuarterFromDate(selectedOperator.date_last_paid || selectedOperator.date_registered) : 'N/A' }}</div>
            </div>
          </div>

          <div class="flex justify-end gap-3">
            <button
              @click="closePrintModal"
              class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300 transition-colors"
              :disabled="idPrintLoading"
            >
              Cancel
            </button>
            <button
              @click="printIdCard"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center gap-2"
              :disabled="idPrintLoading || !bodyNumber.trim() || !stickerNumber.trim() || selectedOperator?.permit_status === 'retire'"
              :class="{'opacity-50 cursor-not-allowed': selectedOperator?.permit_status === 'retire'}"
            >
              <span v-if="idPrintLoading" class="spinner-border inline-block h-4 w-4 animate-spin rounded-full border-2 border-t-white border-green-200"></span>
              {{ selectedOperator?.permit_status === 'retire' ? 'Printing Disabled' : (idPrintLoading ? 'Printing...' : 'Print ID Card') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- All Reports Modal -->
    <div v-if="showReportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold text-gray-900">Printing Activity Report</h3>
            <button
              @click="closePrintModal"
              class="text-gray-500 hover:text-gray-700"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Date Range Filter -->
          <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h4 class="text-lg font-medium text-gray-700 mb-4">Filter by Date Range</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input
                  v-model="reportDateRange.start"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input
                  v-model="reportDateRange.end"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div class="flex items-end">
                <button
                  @click="loadPrintLogs"
                  class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                >
                  Apply Filter
                </button>
              </div>
            </div>
          </div>

          <!-- Statistics -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <div class="text-2xl font-bold text-blue-600">{{ getPrintStats.total }}</div>
              <div class="text-sm text-blue-800 font-medium">Total Prints</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
              <div class="text-2xl font-bold text-green-600">{{ getPrintStats.permits }}</div>
              <div class="text-sm text-green-800 font-medium">Permits Printed</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
              <div class="text-2xl font-bold text-purple-600">{{ getPrintStats.id_cards }}</div>
              <div class="text-sm text-purple-800 font-medium">ID Cards Printed</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="text-sm font-medium text-gray-600">Date Range</div>
              <div class="text-sm text-gray-800">{{ getPrintStats.date_range }}</div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 mb-6">
            <button
              @click="printReport"
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-2"
              :disabled="reportLoading"
            >
              <span v-if="reportLoading" class="spinner-border inline-block h-4 w-4 animate-spin rounded-full border-2 border-t-white border-blue-200"></span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print Report
            </button>
            <button
              @click="exportReport"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export CSV
            </button>
            <button
              @click="clearAllLogs"
              class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors flex items-center gap-2 ml-auto"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Clear All Logs
            </button>
          </div>

          <!-- Logs Table -->
          <div class="overflow-auto max-h-96">
            <table class="w-full text-left border-collapse">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="p-3 font-semibold text-gray-700 border-b">Date & Time</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Type</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Operator</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Driver</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">MTOP No.</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">OR Number</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Body No.</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Sticker No.</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Quarter</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-gray-50">
                  <td class="p-3 border-b text-sm">{{ log.printed_at }}</td>
                  <td class="p-3 border-b">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', log.type === 'permit' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800']">
                      {{ log.type === 'permit' ? 'Permit' : 'ID Card' }}
                    </span>
                  </td>
                  <td class="p-3 border-b text-sm">{{ log.operator_name }}</td>
                  <td class="p-3 border-b text-sm">{{ log.driver_name }}</td>
                  <td class="p-3 border-b text-sm font-mono">{{ log.mtop_number }}</td>
                  <td class="p-3 border-b text-sm">{{ log.or_number || 'N/A' }}</td>
                  <td class="p-3 border-b text-sm">{{ log.body_number || 'N/A' }}</td>
                  <td class="p-3 border-b text-sm">{{ log.sticker_number || 'N/A' }}</td>
                  <td class="p-3 border-b text-sm">{{ log.quarter }}</td>
                  <td class="p-3 border-b text-sm">
                    <button @click="deleteLog(log.id)" class="text-red-600 hover:text-red-800 transition-colors" title="Delete Log">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredLogs.length === 0">
                  <td colspan="10" class="p-6 text-center text-gray-500">
                    No print activity found for the selected date range.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Permit Report Modal -->
    <div v-if="showPermitReportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold text-gray-900">Permit Printing Report</h3>
            <button @click="closePrintModal" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Date Range Filter -->
          <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h4 class="text-lg font-medium text-gray-700 mb-4">Filter by Date Range</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input v-model="reportDateRange.start" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input v-model="reportDateRange.end" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div class="flex items-end">
                <button @click="loadPrintLogs" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Apply Filter</button>
              </div>
            </div>
          </div>

          <!-- Statistics -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <div class="text-2xl font-bold text-blue-600">{{ getPermitStats.total }}</div>
              <div class="text-sm text-blue-800 font-medium">Permits Printed</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="text-sm font-medium text-gray-600">Date Range</div>
              <div class="text-sm text-gray-800">{{ getPermitStats.date_range }}</div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 mb-6">
            <button @click="printPermitReport" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-2" :disabled="reportLoading">
              <span v-if="reportLoading" class="spinner-border inline-block h-4 w-4 animate-spin rounded-full border-2 border-t-white border-blue-200"></span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print Report
            </button>
            <button @click="exportPermitReport" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export CSV
            </button>
            <button @click="clearAllLogs" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors flex items-center gap-2 ml-auto">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Clear All Logs
            </button>
          </div>

          <!-- Logs Table -->
          <div class="overflow-auto max-h-96">
            <table class="w-full text-left border-collapse">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="p-3 font-semibold text-gray-700 border-b">Date & Time</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Operator</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Driver</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">MTOP No.</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">OR Number</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Quarter</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in filteredPermitLogs" :key="log.id" class="hover:bg-gray-50">
                  <td class="p-3 border-b text-sm">{{ log.printed_at }}</td>
                  <td class="p-3 border-b text-sm">{{ log.operator_name }}</td>
                  <td class="p-3 border-b text-sm">{{ log.driver_name }}</td>
                  <td class="p-3 border-b text-sm font-mono">{{ log.mtop_number }}</td>
                  <td class="p-3 border-b text-sm">{{ log.or_number || 'N/A' }}</td>
                  <td class="p-3 border-b text-sm">{{ log.quarter }}</td>
                  <td class="p-3 border-b text-sm">
                    <button @click="deleteLog(log.id)" class="text-red-600 hover:text-red-800 transition-colors" title="Delete Log">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredPermitLogs.length === 0">
                  <td colspan="7" class="p-6 text-center text-gray-500">No permit print activity found for the selected date range.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- ID Report Modal -->
    <div v-if="showIdReportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-semibold text-gray-900">ID Card Printing Report</h3>
            <button @click="closePrintModal" class="text-gray-500 hover:text-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Date Range Filter -->
          <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <h4 class="text-lg font-medium text-gray-700 mb-4">Filter by Date Range</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input v-model="reportDateRange.start" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input v-model="reportDateRange.end" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </div>
              <div class="flex items-end">
                <button @click="loadPrintLogs" class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">Apply Filter</button>
              </div>
            </div>
          </div>

          <!-- Statistics -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
              <div class="text-2xl font-bold text-green-600">{{ getIdStats.total }}</div>
              <div class="text-sm text-green-800 font-medium">ID Cards Printed</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <div class="text-sm font-medium text-gray-600">Date Range</div>
              <div class="text-sm text-gray-800">{{ getIdStats.date_range }}</div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 mb-6">
            <button @click="printIdReport" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors flex items-center gap-2" :disabled="reportLoading">
              <span v-if="reportLoading" class="spinner-border inline-block h-4 w-4 animate-spin rounded-full border-2 border-t-white border-green-200"></span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
              </svg>
              Print Report
            </button>
            <button @click="exportIdReport" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export CSV
            </button>
            <button @click="clearAllLogs" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors flex items-center gap-2 ml-auto">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Clear All Logs
            </button>
          </div>

          <!-- Logs Table -->
          <div class="overflow-auto max-h-96">
            <table class="w-full text-left border-collapse">
              <thead class="bg-gray-50 sticky top-0">
                <tr>
                  <th class="p-3 font-semibold text-gray-700 border-b">Date & Time</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Operator</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Driver</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">MTOP No.</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Body No.</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Sticker No.</th>
                  <th class="p-3 font-semibold text-gray-700 border-b">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in filteredIdLogs" :key="log.id" class="hover:bg-gray-50">
                  <td class="p-3 border-b text-sm">{{ log.printed_at }}</td>
                  <td class="p-3 border-b text-sm">{{ log.operator_name }}</td>
                  <td class="p-3 border-b text-sm">{{ log.driver_name }}</td>
                  <td class="p-3 border-b text-sm font-mono">{{ log.mtop_number }}</td>
                  <td class="p-3 border-b text-sm">{{ log.body_number || 'N/A' }}</td>
                  <td class="p-3 border-b text-sm">{{ log.sticker_number || 'N/A' }}</td>
                  <td class="p-3 border-b text-sm">
                    <button @click="deleteLog(log.id)" class="text-red-600 hover:text-red-800 transition-colors" title="Delete Log">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredIdLogs.length === 0">
                  <td colspan="7" class="p-6 text-center text-gray-500">No ID card print activity found for the selected date range.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
