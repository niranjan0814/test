"use client";

import React, { useState, useEffect } from 'react';
import { Plus, Search } from 'lucide-react';
import { Branch, BranchStats as BranchStatsType, BranchFormData } from '../../types/branch.types';
import { branchService } from '../../services/branch.service';
import { BranchStats } from '../../components/branches/BranchStats';
import { BranchTable } from '../../components/branches/BranchTable';
import { BranchForm } from '../../components/branches/BranchForm';
import { colors } from '../../themes/colors';

export default function BranchManagementPage() {
    const [branches, setBranches] = useState<Branch[]>([]);
    const [loading, setLoading] = useState(true);
    const [showModal, setShowModal] = useState(false);
    const [editingBranch, setEditingBranch] = useState<Branch | null>(null);
    const [searchTerm, setSearchTerm] = useState('');

    // Initial data fetch
    useEffect(() => {
        loadBranches();
    }, []);

    const loadBranches = async () => {
        try {
            setLoading(true);
            const data = await branchService.getBranches();
            setBranches(data);
        } catch (error) {
            console.error('Failed to load branches:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleAdd = () => {
        setEditingBranch(null);
        setShowModal(true);
    };

    const handleEdit = (branch: Branch) => {
        setEditingBranch(branch);
        setShowModal(true);
    };

    const handleDelete = async (id: string) => {
        if (confirm('Are you sure you want to delete this branch?')) {
            try {
                await branchService.deleteBranch(id);
                await loadBranches(); // Reload list
            } catch (error) {
                console.error('Failed to delete branch:', error);
            }
        }
    };

    const handleSave = async (formData: BranchFormData) => {
        try {
            if (editingBranch) {
                await branchService.updateBranch(editingBranch.id, formData);
            } else {
                await branchService.createBranch(formData);
            }
            setShowModal(false);
            loadBranches();
        } catch (error) {
            console.error('Failed to save branch:', error);
        }
    };

    // Filter branches based on search
    const filteredBranches = branches.filter(branch =>
        branch.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        branch.code.toLowerCase().includes(searchTerm.toLowerCase()) ||
        branch.city.toLowerCase().includes(searchTerm.toLowerCase())
    );

    // Calculate statistics
    const stats: BranchStatsType = {
        totalBranches: branches.length,
        activeBranches: branches.filter(b => b.status === 'Active').length,
        totalCustomers: branches.reduce((sum, b) => sum + (b.customerCount || 0), 0),
        totalLoans: branches.reduce((sum, b) => sum + (b.loanCount || 0), 0)
    };

    if (loading && branches.length === 0) {
        return (
            <div className="flex items-center justify-center min-h-screen">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        );
    }

    return (
        <div className="space-y-6 p-6 max-w-7xl mx-auto">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Branch Management</h1>
                    <p className="text-sm text-gray-500 mt-1">Manage all branch locations and details</p>
                </div>
                <button
                    onClick={handleAdd}
                    className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm"
                    style={{ backgroundColor: colors.primary[600] }}
                >
                    <Plus className="w-4 h-4" />
                    <span className="text-sm font-medium">Add Branch</span>
                </button>
            </div>

            {/* Statistics */}
            <BranchStats stats={stats} />

            {/* Search */}
            <div className="bg-white rounded-lg border border-gray-200 p-4">
                <div className="relative">
                    <Search className="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        type="text"
                        placeholder="Search branches by name, code, or city..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>

            {/* Table */}
            <BranchTable
                branches={filteredBranches}
                totalBranches={branches.length}
                onEdit={handleEdit}
                onDelete={handleDelete}
            />

            {/* Modal */}
            <BranchForm
                isOpen={showModal}
                onClose={() => setShowModal(false)}
                onSave={handleSave}
                initialData={editingBranch}
            />
        </div>
    );
}
