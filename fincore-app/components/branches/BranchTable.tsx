'use client'

import React from 'react';
import { Building2, Edit, Trash2, MapPin } from 'lucide-react';
import { Branch } from '../../types/branch.types';
import { colors } from '../../themes/colors';
import { usePagination } from '../../hooks/usePagination';
import { Pagination } from '../common/Pagination';

interface BranchTableProps {
    branches: Branch[];
    totalBranches: number;
    onEdit: (branch: Branch) => void;
    onDelete: (id: number) => void;
}

export function BranchTable({ branches, totalBranches, onEdit, onDelete }: BranchTableProps) {
    const {
        currentPage,
        itemsPerPage,
        startIndex,
        endIndex,
        handlePageChange,
        handleItemsPerPageChange
    } = usePagination({ totalItems: branches.length });

    const currentBranches = branches.slice(startIndex, endIndex);

    return (
        <div className="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div className="bg-gray-50 border-b border-gray-200 px-6 py-3">
                <div className="grid grid-cols-12 gap-4 text-xs font-semibold text-gray-600 uppercase">
                    <div className="col-span-2">Branch ID</div>
                    <div className="col-span-3">Branch Name</div>
                    <div className="col-span-3">Location</div>
                    <div className="col-span-3">Address</div>
                    <div className="col-span-1 text-right">Actions</div>
                </div>
            </div>

            <div className="divide-y divide-gray-100">
                {currentBranches.length === 0 ? (
                    <div className="px-6 py-8 text-center text-gray-500 text-sm">
                        No branches found.
                    </div>
                ) : (
                    currentBranches.map((branch) => (
                        <div key={branch.id} className="px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div className="grid grid-cols-12 gap-4 items-center">
                                {/* Branch ID */}
                                <div className="col-span-2">
                                    <span className="text-sm font-medium text-gray-900">{branch.branch_id}</span>
                                </div>

                                {/* Branch Name */}
                                <div className="col-span-3 flex items-center gap-3">
                                    <div className="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style={{ backgroundColor: colors.primary[600] }}>
                                        <Building2 className="w-4 h-4 text-white" />
                                    </div>
                                    <p className="text-sm font-medium text-gray-900 truncate">{branch.branch_name}</p>
                                </div>

                                {/* Location */}
                                <div className="col-span-3 flex items-center gap-2">
                                    <MapPin className="w-4 h-4 text-gray-400 flex-shrink-0" />
                                    <span className="text-sm text-gray-600">{branch.location}</span>
                                </div>

                                {/* Address */}
                                <div className="col-span-3">
                                    <p className="text-sm text-gray-600 truncate" title={branch.address || ''}>
                                        {branch.address || '-'}
                                    </p>
                                </div>

                                {/* Actions */}
                                <div className="col-span-1 flex items-center justify-end gap-2">
                                    <button
                                        onClick={() => onEdit(branch)}
                                        className="p-1.5 hover:bg-blue-50 rounded text-blue-600"
                                        title="Edit"
                                    >
                                        <Edit className="w-4 h-4" />
                                    </button>
                                    <button
                                        onClick={() => onDelete(branch.id)}
                                        className="p-1.5 hover:bg-red-50 rounded text-red-600"
                                        title="Delete"
                                    >
                                        <Trash2 className="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* Pagination */}
            <Pagination
                currentPage={currentPage}
                totalItems={branches.length}
                itemsPerPage={itemsPerPage}
                onPageChange={handlePageChange}
                onItemsPerPageChange={handleItemsPerPageChange}
                itemName="branches"
            />
        </div>
    );
}
