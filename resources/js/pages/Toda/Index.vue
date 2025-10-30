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
const baseentityurl = '/toda';
const baseentityname = 'TODA';

/* Breadcrumbs */
const breadcrumbs: BreadcrumbItem[] = [
  {
    title: baseentityname,
    href: baseentityurl,
  },
];

const todaStatusEnum = {
  active: 'Active',
  inactive: 'Inactive',
};

/* Define Props */
export interface Toda {
  id: number;
  toda_name: string;
  toda_president: string;
  date_registered: string;
  toda_status: string;
}

/* Define Table Columns */
const columns: ColumnDef<Toda>[] = [
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
    accessorKey: 'toda_name',
    header: ({ column }) =>
      h(
        Button,
        {
          variant: 'ghost',
          onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        },
        () => ['TODA Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
      ),
    cell: ({ row }) => h('div', { class: 'break-words whitespace-normal' }, row.getValue('toda_name')),
  },
  {
    accessorKey: 'toda_president',
    header: 'President',
    cell: ({ row }) => h('div', {}, row.getValue('toda_president'))
  },
  {
    accessorKey: 'date_registered',
    header: 'Date Registered',
    cell: ({ row }) => h('div', {}, row.getValue('date_registered'))
  },
  {
    accessorKey: 'toda_status',
    header: 'Status',
    cell: ({ row }) => {
      const status = row.original.toda_status;
      const statusColors = {
        [todaStatusEnum.active]: 'text-green-500',
        [todaStatusEnum.inactive]: 'text-red-500',
      };
      return h('div', { class: `break-words whitespace-normal ${statusColors[status] || 'text-black'}` }, status);
    },
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
  toda_name: z.string().min(3).max(255),
  toda_president: z.string().min(3).max(255),
  date_registered: z.coerce.date(),
  toda_status: z.enum(Object.keys(todaStatusEnum) as [string, ...string[]]),
});

const fieldconfig: any = {
  toda_name: { label: 'TODA Name', inputProps: { type: 'text', class: 'uppercase', placeholder: 'Enter TODA name' } },
  toda_president: { label: 'TODA President', inputProps: { type: 'text', class: 'uppercase', placeholder: 'Enter president name' } },
  date_registered: { label: 'Date Registered', inputProps: { type: 'date' } },
  toda_status: { label: 'TODA Status', component: 'select' },
};

const form = useForm({
  validationSchema: toTypedSchema(schema),
  initialValues: {
    toda_name: '',
    toda_president: '',
    date_registered: null,
    toda_status: null,
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
    if (mode.value === 'create') {
      await axios.post(`${baseentityurl}`, values);
      toast.success(`${baseentityname} created successfully.`);
    } else {
      await axios.put(`${baseentityurl}/${itemID.value}`, values);
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
      date_registered: data.date_registered ? parseDate(data.date_registered.slice(0, 10)) : null,
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
        <DialogScrollContent class="sm:max-w-[600px]">
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
