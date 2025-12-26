'use client'

import React, { useState, useEffect } from 'react';
import { GroupFormData } from '../../types/group.types';
import { Branch } from '../../types/branch.types';
import { Center } from '../../types/center.types';
import { branchService } from '../../services/branch.service';
import { centerService } from '../../services/center.service';
import { X } from 'lucide-react';

interface GroupFormProps {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: (data: GroupFormData) => void;
    initialData?: GroupFormData | null;
}

export function GroupForm({ isOpen, onClose, onSubmit, initialData }: GroupFormProps) {
    const [branches, setBranches] = useState<Branch[]>([]);
    const [centers, setCenters] = useState<Center[]>([]);
    const [isLoadingData, setIsLoadingData] = useState(false);

    useEffect(() => {
        const loadFormData = async () => {
            setIsLoadingData(true);
            try {
                const [branchesData, centersData] = await Promise.all([
                    branchService.getBranches(),
                    centerService.getCenters()
                ]);
                setBranches(branchesData);
                setCenters(centersData);
            } catch (error) {
                console.error('Failed to load form data:', error);
            } finally {
                setIsLoadingData(false);
            }
        };
        if (isOpen) {
            loadFormData();
        }
    }, [isOpen]);

    if (!isOpen) return null;

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const formData = new FormData(e.currentTarget);

        const data: GroupFormData = {
            group_name: formData.get('groupName') as string,
            center_id: formData.get('center') as string,
            branch_id: formData.get('branch') as string,
            status: (initialData?.status || 'active') as 'active' | 'inactive'
        };
        onSubmit(data);
    };

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg max-w-lg w-full shadow-xl">
                <div className="p-6 border-b border-gray-200">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-gray-900">
                            {initialData ? 'Edit Group' : 'Add New Group'}
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
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Group Name *
                        </label>
                        <input
                            type="text"
                            name="groupName"
                            required
                            defaultValue={initialData?.group_name}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Enter group name"
                        />
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Center *
                        </label>
                        <select
                            name="center"
                            required
                            defaultValue={initialData?.center_id}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            disabled={isLoadingData}
                        >
                            <option value="">Select Center</option>
                            {centers.map((center) => (
                                <option key={center.id} value={center.id}>
                                    {center.center_name} ({center.CSU_id})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Branch
                        </label>
                        <select
                            name="branch"
                            defaultValue={initialData?.branch_id}
                            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            disabled={isLoadingData}
                        >
                            <option value="">Select Branch (Optional)</option>
                            {branches.map((branch) => (
                                <option key={branch.id} value={branch.branch_id}>
                                    {branch.branch_name} ({branch.branch_id})
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="p-4 border-t border-gray-200 flex gap-3 justify-end bg-gray-50 -mx-6 -mb-6 mt-6">
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
                            disabled={isLoadingData}
                        >
                            {initialData ? 'Update Group' : 'Add Group'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}
