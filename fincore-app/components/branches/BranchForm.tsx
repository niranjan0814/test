import React, { useEffect, useState } from 'react';
import { X } from 'lucide-react';
import { Branch, BranchFormData } from '../../types/branch.types';

interface BranchFormProps {
    isOpen: boolean;
    onClose: () => void;
    onSave: (data: BranchFormData) => void;
    initialData?: Branch | null;
}

const defaultFormData: BranchFormData = {
    name: '',
    address: '',
    city: '',
    state: '',
    pincode: '',
    phone: '',
    email: '',
    manager: ''
};

export function BranchForm({ isOpen, onClose, onSave, initialData }: BranchFormProps) {
    const [formData, setFormData] = useState<BranchFormData>(defaultFormData);

    useEffect(() => {
        if (initialData) {
            setFormData({
                name: initialData.name,
                address: initialData.address,
                city: initialData.city,
                state: initialData.state,
                pincode: initialData.pincode,
                phone: initialData.phone,
                email: initialData.email,
                manager: initialData.manager
            });
        } else {
            setFormData(defaultFormData);
        }
    }, [initialData, isOpen]);

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
                <div className="p-6 border-b border-gray-200">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-gray-900">
                            {initialData ? 'Edit Branch' : 'Add New Branch'}
                        </h2>
                        <button
                            onClick={onClose}
                            className="p-1 hover:bg-gray-100 rounded transition-colors"
                        >
                            <X className="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                </div>

                <div className="p-6 space-y-4">
                    <div className="grid grid-cols-2 gap-4">
                        <div className="col-span-2">
                            <label className="block font-medium text-gray-900 mb-2 text-sm">Branch Name *</label>
                            <input
                                type="text"
                                value={formData.name}
                                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="Enter branch name"
                            />
                        </div>

                        <div className="col-span-2">
                            <label className="block font-medium text-gray-900 mb-2 text-sm">Address *</label>
                            <textarea
                                value={formData.address}
                                onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                                rows={2}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none text-sm"
                                placeholder="Enter address"
                            />
                        </div>

                        <div>
                            <label className="block font-medium text-gray-900 mb-2 text-sm">City *</label>
                            <input
                                type="text"
                                value={formData.city}
                                onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="Enter city"
                            />
                        </div>

                        <div>
                            <label className="block font-medium text-gray-900 mb-2 text-sm">Province *</label>
                            <input
                                type="text"
                                value={formData.state}
                                onChange={(e) => setFormData({ ...formData, state: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="Enter province"
                            />
                        </div>

                        <div>
                            <label className="block font-medium text-gray-900 mb-2 text-sm">Postal Code *</label>
                            <input
                                type="text"
                                value={formData.pincode}
                                onChange={(e) => setFormData({ ...formData, pincode: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="Enter postal code"
                            />
                        </div>

                        <div>
                            <label className="block font-medium text-gray-900 mb-2 text-sm">Phone *</label>
                            <input
                                type="tel"
                                value={formData.phone}
                                onChange={(e) => setFormData({ ...formData, phone: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="+94 XX XXX XXXX"
                            />
                        </div>

                        <div>
                            <label className="block font-medium text-gray-900 mb-2 text-sm">Email *</label>
                            <input
                                type="email"
                                value={formData.email}
                                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="branch@lms.lk"
                            />
                        </div>

                        <div>
                            <label className="block font-medium text-gray-900 mb-2 text-sm">Branch Manager *</label>
                            <input
                                type="text"
                                value={formData.manager}
                                onChange={(e) => setFormData({ ...formData, manager: e.target.value })}
                                className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                                placeholder="Enter manager name"
                            />
                        </div>
                    </div>
                </div>

                <div className="p-4 border-t border-gray-200 flex gap-3 justify-end bg-gray-50">
                    <button
                        onClick={onClose}
                        className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-white transition-colors font-medium text-sm"
                    >
                        Cancel
                    </button>
                    <button
                        onClick={() => onSave(formData)}
                        className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm"
                    >
                        {initialData ? 'Update Branch' : 'Add Branch'}
                    </button>
                </div>
            </div>
        </div>
    );
}
