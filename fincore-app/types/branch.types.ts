export interface Branch {
    id: string;
    code: string;
    name: string;
    address: string;
    city: string;
    state: string;
    pincode: string;
    phone: string;
    email: string;
    manager: string;
    status: 'Active' | 'Inactive';
    customerCount?: number;
    loanCount?: number;
}

export interface BranchFormData {
    name: string;
    address: string;
    city: string;
    state: string;
    pincode: string;
    phone: string;
    email: string;
    manager: string;
}

export interface BranchStats {
    totalBranches: number;
    activeBranches: number;
    totalCustomers: number;
    totalLoans: number;
}
