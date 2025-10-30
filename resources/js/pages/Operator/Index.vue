<script setup lang="ts">
/* Import Components */
import ReusableAlertDialog from '@/components/entitycomponents/ReusableAlertDialog.vue';
import ReusableDropDownAction from '@/components/entitycomponents/ReusableDropDownAction.vue';
import ReusableDataTable from '@/components/entitycomponents/ReusableDataTable.vue';
import { AutoForm } from '@/components/ui/auto-form';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogDescription, DialogFooter, DialogHeader, DialogScrollContent, DialogTitle } from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

/* Import Utilities */
import { parseDate } from "@internationalized/date";
import { toTypedSchema } from '@vee-validate/zod';
import axios from 'axios';
import { ArrowUpDown, Plus } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { h, ref } from 'vue';
import { toast } from 'vue-sonner';
import * as z from 'zod';

/* Import Table Utilities */
import type { ColumnDef } from '@tanstack/vue-table';

/* Import Types */
import { BreadcrumbItem } from '@/types';

/* Base Entity Configuration */
const baseentityurl = '/operators';
const baseentityname = 'Operator';

/* Breadcrumbs */
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: baseentityname,
    href: baseentityurl,
  },
];

const props = defineProps({
  drivers: { type: Object, required: true },
  motorcycles: { type: Object, required: true },
  toda: { type: Object, required: true },
});

/* Define Props */
export interface Operator {
  id: number;
  fullname: string;
  address: string;
  email_address: string;
  contact_number: string;
  driver_id: number;
  driver_fullname: string;
  driver_license_number: string;
  motorcycle_id: number;
  plate_number: string;
  mtop_number: string;
  toda_id: number;
  toda_name: string;
  date_registered: string;
  date_last_paid: string | null;
  permit_status: string;
}

const permitStatusEnum = {
  new: 'New',
  renew: 'Renew',
  retire: 'Retire',
};

/* Define Table Columns */
const columns: ColumnDef<Operator>[] = [
  {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        modelValue: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
        'onUpdate:modelValue': (value) => table.toggleAllPageRowsSelected(!!value),
        ariaLabel: 'Select all',
      }),
    cell: ({ row }) =>
      h(Checkbox, {
        modelValue: row.getIsSelected(),
        'onUpdate:modelValue': (value) => row.toggleSelected(!!value),
        ariaLabel: 'Select row',
      }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'fullname',
    header: ({ column }) =>
      h(
        Button,
        {
          variant: 'ghost',
          onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        },
        () => ['Full Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
      ),
    cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.getValue('fullname')),
  },
  {
    accessorKey: 'email_address',
    header: 'Email Address',
    cell: ({ row }) => h('div', {}, row.getValue('email_address'))
  },
  {
    accessorKey: 'contact_number',
    header: 'Contact Number',
    cell: ({ row }) => h('div', {}, row.getValue('contact_number'))
  },
  {
    accessorKey: 'driver_fullname',
    header: 'Driver',
    cell: ({ row }) => h('div', {}, row.original.driver_fullname || '')
  },
  {
    accessorKey: 'plate_number',
    header: 'Motorcycle',
    cell: ({ row }) => h('div', {}, row.original.plate_number || '')
  },
  {
    accessorKey: 'mtop_number',
    header: 'MTOP Number',
    cell: ({ row }) => h('div', {}, row.getValue('mtop_number'))
  },
  {
    accessorKey: 'toda_name',
    header: 'TODA',
    cell: ({ row }) => h('div', {}, row.original.toda_name || '')
  },
  {
    accessorKey: 'permit_status',
    header: 'Permit Status',
    cell: ({ row }) => {
      const status = row.original.permit_status;
      const statusColors = {
        [permitStatusEnum.new]: 'text-green-500',
        [permitStatusEnum.renew]: 'text-blue-500',
        [permitStatusEnum.retire]: 'text-red-500',
      };
      return h('div', { class: `break-words whitespace-normal ${statusColors[status] || 'text-black'}` }, status);
    },
  },
  {
    accessorKey: 'date_registered',
    header: 'Date Registered',
    cell: ({ row }) => h('div', {}, row.getValue('date_registered'))
  },
  {
    id: 'actions',
    enableHiding: false,
    cell: ({ row }) => {
      const rowitem = row.original;
      return h('div', { class: 'relative' },
        h(ReusableDropDownAction, { rowitem, onEdit: handleEdit, onDelete: openDeleteDialog })
      );
    },
  },
];

/* Dialog State */
const showDialogForm = ref(false);
const mode = ref('create');
const itemID = ref<number | null>(null);

/* Form Schema and Config */
const schema = z.object({
  fullname: z.string().min(3).max(255),
  address: z.string().min(5).max(500),
  email_address: z.string().email(),
  contact_number: z.string().min(10).max(20),
  driver_id: z.enum(props.drivers.map((item: any) => item.driver_fullname)),
  motorcycle_id: z.enum(props.motorcycles.map((item: any) => item.plate_number)),
  mtop_number: z.string().min(3).max(50),
  toda_id: z.enum(props.toda.map((item: any) => item.toda_name)),
  date_registered: z.coerce.date(),
  date_last_paid: z.coerce.date().nullable().optional(),
  permit_status: z.enum(Object.keys(permitStatusEnum) as [string, ...string[]]),
});

const fieldconfig: any = {
  fullname: { label: 'Full Name', inputProps: { type: 'text', class: 'uppercase', placeholder: 'Enter operator full name' } },
  address: { label: 'Address', component: 'textarea', inputProps: { class: 'uppercase', placeholder: 'Enter complete address' } },
  email_address: { label: 'Email Address', inputProps: { type: 'email', placeholder: 'Enter email address' } },
  contact_number: { label: 'Contact Number', inputProps: { type: 'text', placeholder: 'Enter contact number' } },
  driver_id: { label: 'Select Driver', component: 'select', inputProps: { placeholder: 'Choose a driver...' } },
  motorcycle_id: { label: 'Select Motorcycle', component: 'select', inputProps: { placeholder: 'Choose a motorcycle...' } },
  mtop_number: { label: 'MTOP Number', inputProps: { type: 'text', class: 'uppercase', placeholder: 'Enter MTOP number' } },
  toda_id: { label: 'Select TODA', component: 'select', inputProps: { placeholder: 'Choose a TODA...' } },
  date_registered: { label: 'Date Registered', inputProps: { type: 'date' } },
  date_last_paid: { label: 'Date Last Paid', inputProps: { type: 'date' } },
  permit_status: { label: 'Permit Status', component: 'select' },
};

const form = useForm({
  validationSchema: toTypedSchema(schema),
  initialValues: {
    fullname: '',
    address: '',
    email_address: '',
    contact_number: '',
    driver_id: null,
    motorcycle_id: null,
    mtop_number: '',
    toda_id: null,
    date_registered: null,
    date_last_paid: null,
    permit_status: null,
  },
});

/* Reset Form */
const resetForm = () => {
  form.resetForm();
  itemID.value = null;
};

/* Open Create Form */
const handleOpenDialogForm = () => {
  showDialogForm.value = true;
  mode.value = 'create';
};

const tableRef = ref<InstanceType<typeof ReusableDataTable> | null>(null);

/* Submit Handler */
const onSubmit = async (values: any) => {
  try {
    const mappedValues = {
      ...values,
      driver_id: props.drivers.find((d: any) => d.driver_fullname === values.driver_id)?.id || null,
      motorcycle_id: props.motorcycles.find((m: any) => m.plate_number === values.motorcycle_id)?.id || null,
      toda_id: props.toda.find((t: any) => t.toda_name === values.toda_id)?.id || null,
    };

    if (mode.value === 'create') {
      await axios.post(`${baseentityurl}`, mappedValues);
      toast.success(`${baseentityname} created successfully.`);
    } else {
      await axios.put(`${baseentityurl}/${itemID.value}`, mappedValues);
      toast.success(`${baseentityname} updated successfully.`);
    }
    resetForm();
    await tableRef.value?.fetchRows();
    showDialogForm.value = false;
  } catch (error: any) {
    if (error.response?.status === 422) {
      form.setErrors(error.response.data.errors);
      toast.error('Validation failed.');
    } else {
      toast.error('An unexpected error occurred.');
    }
  }
};

/* Edit Handler */
const handleEdit = async (id: number) => {
  try {
    mode.value = 'edit';
    itemID.value = id;
    const response = await axios.get(`${baseentityurl}/${id}`);
    const data = response.data;
    const mappedData = {
      ...data,
      driver_id: data.driver?.driver_fullname,
      motorcycle_id: data.motorcycle?.plate_number,
      toda_id: data.toda?.toda_name,
      date_registered: data.date_registered ? parseDate(data.date_registered.slice(0, 10)) : null,
      date_last_paid: data.date_last_paid ? parseDate(data.date_last_paid.slice(0, 10)) : null,
    };
    form.setValues(mappedData);
    showDialogForm.value = true;
  } catch (error) {
    toast.error(`Failed to fetch ${baseentityname} data.`);
  }
};

/* Delete Logic */
const showDeleteDialog = ref(false);
const itemIDToDelete = ref<number | null>(null);

const openDeleteDialog = (id: number) => {
  itemIDToDelete.value = id;
  showDeleteDialog.value = true;
};

const handleDelete = async () => {
  try {
    if (!itemIDToDelete.value) return;
    await axios.delete(`${baseentityurl}/${itemIDToDelete.value}`);
    toast.success(`${baseentityname} deleted successfully.`);
    await tableRef.value?.fetchRows();
    showDeleteDialog.value = false;
  } catch {
    toast.error(`Failed to delete ${baseentityname}.`);
  }
};

/* Print Handler */
const handlePrint = () => {
  const tableEl = document.getElementById("print-section");
  if (tableEl) {
    const printContents = tableEl.outerHTML;
    const printWindow = window.open("", "", "height=800,width=1100");
    if (printWindow) {
      printWindow.document.write(`
        <html>
          <head>
            <title>Print ${baseentityname}</title>
            <style>
              body { font-family: sans-serif; padding: 20px; }
              table { width: 100%; border-collapse: collapse; }
              th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
              th { background-color: #f4f4f4; }
              button, .actions, .checkbox, [role="checkbox"] { display: none !important; }
            </style>
          </head>
          <body>
            <h2 style="margin-bottom: 10px;">${baseentityname} List</h2>
            ${printContents}
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    }
  }
};
</script>

<template>
  <Head :title="baseentityname" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-2 rounded-xl p-4">
      <!-- Action Buttons -->
      <div class="flex items-center gap-2 py-2">
        <div class="ml-auto flex items-center gap-2">
          <Button class="bg-green-600" @click="handlePrint">🖨 Print</Button>
          <Button class="bg-blue-600" @click="handleOpenDialogForm">
            <Plus class="h-4" /> Create {{ baseentityname }}
          </Button>
        </div>
      </div>

      <!-- Table Section (Printable) -->
      <div id="print-section">
        <ReusableDataTable
          ref="tableRef"
          :columns="columns"
          :baseentityname="baseentityname"
          :baseentityurl="baseentityurl"
        />
      </div>

      <!-- Dialog Form -->
      <Dialog v-model:open="showDialogForm">
        <DialogScrollContent class="sm:max-w-[800px]">
          <DialogHeader>
            <DialogTitle>{{ mode === 'create' ? 'Create' : 'Update' }} {{ baseentityname }}</DialogTitle>
          </DialogHeader>
          <DialogDescription>
            Use this form to edit the {{ baseentityname }} details.
          </DialogDescription>
          <AutoForm class="space-y-6" :form="form" :schema="schema" :field-config="fieldconfig" @submit="onSubmit">
            <DialogFooter>
              <Button type="submit" class="bg-yellow-600">
                {{ mode === 'create' ? 'Create' : 'Update' }}
              </Button>
            </DialogFooter>
          </AutoForm>
        </DialogScrollContent>
      </Dialog>

      <!-- Delete Confirmation -->
      <ReusableAlertDialog
        :open="showDeleteDialog"
        :title="`Delete ${baseentityname}`"
        :description="`Are you sure you want to delete this ${baseentityname}? This action cannot be undone.`"
        @confirm="handleDelete"
        @cancel="showDeleteDialog = false"
      />
    </div>
  </AppLayout>
</template>
