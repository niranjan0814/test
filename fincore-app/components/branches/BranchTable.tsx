import React from 'react';
import { Building2, Phone, Mail, MapPin, Edit, Trash2 } from 'lucide-react';
import { Branch } from '../../types/branch.types';
import { colors } from '../../themes/colors';

interface BranchTableProps {
    branches: Branch[];
    totalBranches: number;
    onEdit: (branch: Branch) => void;
    onDelete: (id: string) => void;
}

export function BranchTable({ branches, totalBranches, onEdit, onDelete }: BranchTableProps) {
    return (
        <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div className="bg-gray-50 border-b border-gray-200 px-6 py-3">
                <div className="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-600 uppercase">
                    <div className="col-span-3">Branch</div>
                    <div className="col-span-3">Contact</div>
                    <div className="col-span-2">Location</div>
                    <div className="col-span-2">Manager</div>
                    <div className="col-span-1">Status</div>
                    <div className="col-span-1">Actions</div>
                </div>
            </div>

            <div className="divide-y divide-gray-100">
                {branches.map((branch) => (
                    <div key={branch.id} className="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div className="grid grid-cols-12 gap-4 items-center">
                            {/* Branch Info */}
                            <div className="col-span-3 flex items-center gap-3">
                                <div className="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style={{ backgroundColor: colors.primary[600] }}>
                                    <Building2 className="w-5 h-5 text-white" />
                                </div>
                                <div className="min-w-0">
                                    <p className="font-medium text-gray-900 truncate">{branch.name}</p>
                                    <p className="text-xs text-gray-500">{branch.code}</p>
                                </div>
                            </div>

                            {/* Contact */}
                            <div className="col-span-3">
                                <p className="text-sm text-gray-900 flex items-center gap-1">
                                    <Phone className="w-3 h-3 text-gray-400" />
                                    {branch.phone}
                                </p>
                                <p className="text-xs text-gray-500 flex items-center gap-1 mt-1">
                                    <Mail className="w-3 h-3 text-gray-400" />
                                    {branch.email}
                                </p>
                            </div>

                            {/* Location */}
                            <div className="col-span-2">
                                <p className="text-sm text-gray-900 flex items-center gap-1">
                                    <MapPin className="w-3 h-3 text-gray-400" />
                                    {branch.city}
                                </p>
                                <p className="text-xs text-gray-500">{branch.state}</p>
                            </div>

                            {/* Manager */}
                            <div className="col-span-2">
                                <p className="text-sm text-gray-900">{branch.manager}</p>
                                {branch.customerCount !== undefined && (
                                    <p className="text-xs text-gray-500">{branch.customerCount} customers</p>
                                )}
                            </div>

                            {/* Status */}
                            <div className="col-span-1">
                                <span className={`inline-flex items-center px-2 py-1 rounded text-xs font-medium ${branch.status === 'Active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-700'
                                    }`}>
                                    {branch.status}
                                </span>
                            </div>

                            {/* Actions */}
                            <div className="col-span-1 flex items-center gap-2">
                                <button
                                    onClick={() => onEdit(branch)}
                                    className="p-1.5 hover:bg-blue-50 rounded text-blue-600"
                                >
                                    <Edit className="w-4 h-4" />
                                </button>
                                <button
                                    onClick={() => onDelete(branch.id)}
                                    className="p-1.5 hover:bg-red-50 rounded text-red-600"
                                >
                                    <Trash2 className="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                ))}
            </div>

            {/* Pagination */}
            <div className="bg-gray-50 border-t border-gray-200 px-6 py-3">
                <div className="flex items-center justify-between">
                    <p className="text-sm text-gray-600">
                        Showing <span className="font-medium">{branches.length}</span> of <span className="font-medium">{totalBranches}</span> branches
                    </p>
                    <div className="flex gap-2">
                        <button className="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-white disabled:opacity-50">
                            Previous
                        </button>
                        <button className="px-3 py-1 bg-blue-600 text-white rounded text-sm">
                            1
                        </button>
                        <button className="px-3 py-1 border border-gray-300 rounded text-sm text-gray-700 hover:bg-white disabled:opacity-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
