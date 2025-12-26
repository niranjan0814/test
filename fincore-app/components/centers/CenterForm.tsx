import React, { useState, useEffect } from 'react';
import { CenterFormData, ScheduleItem } from '../../types/center.types';
import { Branch } from '../../types/branch.types';
import { Staff } from '../../types/staff.types';
import { branchService } from '../../services/branch.service';
import { staffService } from '../../services/staff.service';
import { X, Plus, Trash2, Loader2 } from 'lucide-react';

interface CenterFormProps {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: (data: CenterFormData) => void;
    initialData?: CenterFormData | null;
}

export function CenterForm({ isOpen, onClose, onSubmit, initialData }: CenterFormProps) {
    const [schedules, setSchedules] = useState<ScheduleItem[]>([]);
    const [branches, setBranches] = useState<Branch[]>([]);
    const [staffList, setStaffList] = useState<Staff[]>([]);
    const [isLoadingData, setIsLoadingData] = useState(false);

    useEffect(() => {
        const loadFormData = async () => {
            setIsLoadingData(true);
            try {
                const [branchesData, staffData] = await Promise.all([
                    branchService.getBranches(),
                    staffService.getStaffList()
                ]);
                setBranches(branchesData);
                setStaffList(staffData);
            } catch (error) {
                console.error('Failed to load form data:', error);
            } finally {
                setIsLoadingData(false);
            }
        };
        loadFormData();
    }, []);

    useEffect(() => {
        if (initialData?.open_days && initialData.open_days.length > 0) {
            setSchedules(initialData.open_days);
        } else {
            setSchedules([{ day: 'Monday', time: '09:00' }]);
        }
    }, [initialData]);

    const getDayFromDate = (dateString: string) => {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return days[new Date(dateString).getDay()];
    };

    const handleAddSchedule = () => {
        const now = new Date().toISOString().slice(0, 16);
        setSchedules([...schedules, { day: getDayFromDate(now), time: now.split('T')[1], date: now }]);
    };

    const handleRemoveSchedule = (index: number) => {
        if (schedules.length > 1) {
            const newSchedules = schedules.filter((_, i) => i !== index);
            setSchedules(newSchedules);
        }
    };

    const handleDateTimeChange = (index: number, value: string) => {
        if (!value) return;
        const newSchedules = [...schedules];
        const day = getDayFromDate(value);
        const time = value.split('T')[1];

        newSchedules[index] = {
            day,
            time,
            date: value
        };
        setSchedules(newSchedules);
    };

    const handleDayChange = (index: number, day: string) => {
        const newSchedules = [...schedules];
        newSchedules[index] = { ...newSchedules[index], day };
        setSchedules(newSchedules);
    };

    if (!isOpen) return null;

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);

        const data: CenterFormData = {
            CSU_id: formData.get('centerNumber') as string,
            center_name: formData.get('name') as string,
            branch_id: formData.get('branch') as string,
            staff_id: formData.get('contactPerson') as string,
            address: formData.get('address') as string,
            location: formData.get('locationType') as string,
            status: (initialData?.status || 'active') as 'active' | 'inactive',
            open_days: schedules,
            contactPhone: formData.get('contactPhone') as string
        };
        onSubmit(data);
    };

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div className="p-6 border-b border-gray-200">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-gray-900">
                            {initialData ? 'Edit Center' : 'Create New Center'}
                        </h2>
                        <button
                            onClick={onClose}
                            className="p-1 hover:bg-gray-100 rounded transition-colors"
                        >
                            <X className="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Center Number *
                            </label>
                            <input
                                type="text"
                                name="centerNumber"
                                required
                                defaultValue={initialData?.CSU_id}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g., CSU004"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Center Name *
                            </label>
                            <input
                                type="text"
                                name="name"
                                required
                                defaultValue={initialData?.center_name}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g., Jaffna CSU"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Branch *
                            </label>
                            <select
                                name="branch"
                                required
                                defaultValue={initialData?.branch_id}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select Branch</option>
                                {branches.map((branch) => (
                                    <option key={branch.id} value={branch.branch_id}>
                                        {branch.branch_name} ({branch.branch_id})
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Location Type *
                            </label>
                            <select
                                name="locationType"
                                required
                                defaultValue={initialData?.address}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">Select location type</option>
                                <option value="Urban">Urban</option>
                                <option value="Rural">Rural</option>
                                <option value="Semi-Urban">Semi-Urban</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Address *
                        </label>
                        <textarea
                            name="address"
                            required
                            rows={2}
                            defaultValue={initialData?.address}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Full address of the center"
                        />
                    </div>

                    <div className="space-y-3">
                        <label className="block text-sm font-medium text-gray-700">
                            Meeting Schedules *
                        </label>
                        {/* <label className="block text-xs text-gray-500 mb-1">Date & Time</label> */}
                        {schedules.map((schedule, index) => (
                            <div key={index} className="flex gap-4 items-end">
                                <div className="flex-1">
                                    <input
                                        type="datetime-local"
                                        value={schedule.date || ''}
                                        onChange={(e) => handleDateTimeChange(index, e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        required
                                    />
                                </div>
                                <div className="w-32">
                                    <select
                                        value={schedule.day}
                                        onChange={(e) => handleDayChange(index, e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                        <option value="Sunday">Sunday</option>
                                    </select>
                                </div>
                                {schedules.length > 1 && (
                                    <button
                                        type="button"
                                        onClick={() => handleRemoveSchedule(index)}
                                        className="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors mb-[1px]"
                                    >
                                        <Trash2 className="w-5 h-5" />
                                    </button>
                                )}
                            </div>
                        ))}
                        <button
                            type="button"
                            onClick={handleAddSchedule}
                            className="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium mt-2"
                        >
                            <Plus className="w-4 h-4" />
                            Add Another Schedule
                        </button>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Center User (Staff) *
                        </label>
                        <select
                            name="contactPerson"
                            required
                            defaultValue={initialData?.staff_id}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Select Staff Member</option>
                            {staffList.map((staff) => (
                                <option key={staff.staff_id} value={staff.staff_id}>
                                    {staff.full_name} ({staff.staff_id})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="p-4 border-t border-gray-200 flex gap-3 justify-end bg-gray-50">
                        <button
                            type="button"
                            onClick={onClose}
                            className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white transition-colors font-medium text-sm"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm"
                        >
                            {initialData ? 'Update Center' : 'Create Center'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
