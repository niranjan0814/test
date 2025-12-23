import { Branch, BranchFormData } from '../types/branch.types';

// Mock data to simulate API behavior until backend connection is fully established
let mockBranches: Branch[] = [
    {
        id: '1',
        code: 'BR001',
        name: 'Head Office - Colombo',
        address: '123 Galle Road',
        city: 'Colombo',
        state: 'Western Province',
        pincode: '00300',
        phone: '+94 11 234 5678',
        email: 'headoffice@lms.lk',
        manager: 'Nimal Perera',
        status: 'Active',
        customerCount: 450,
        loanCount: 280
    },
    {
        id: '2',
        code: 'BR002',
        name: 'Kandy Branch',
        address: '456 Peradeniya Road',
        city: 'Kandy',
        state: 'Central Province',
        pincode: '20000',
        phone: '+94 81 234 5678',
        email: 'kandy@lms.lk',
        manager: 'Saman Silva',
        status: 'Active',
        customerCount: 320,
        loanCount: 195
    },
    {
        id: '3',
        code: 'BR003',
        name: 'Galle Branch',
        address: '789 Matara Road',
        city: 'Galle',
        state: 'Southern Province',
        pincode: '80000',
        phone: '+94 91 234 5678',
        email: 'galle@lms.lk',
        manager: 'Kamala Fernando',
        status: 'Active',
        customerCount: 280,
        loanCount: 165
    }
];

export const branchService = {
    // Get all branches
    getBranches: async (): Promise<Branch[]> => {
        // Simulate API delay
        return new Promise((resolve) => {
            setTimeout(() => resolve([...mockBranches]), 500);
        });
    },

    // Get single branch
    getBranchById: async (id: string): Promise<Branch | undefined> => {
        return new Promise((resolve) => {
            setTimeout(() => resolve(mockBranches.find(b => b.id === id)), 300);
        });
    },

    // Create new branch
    createBranch: async (data: BranchFormData): Promise<Branch> => {
        return new Promise((resolve) => {
            setTimeout(() => {
                const newBranch: Branch = {
                    id: String(mockBranches.length + 1),
                    code: `BR${String(mockBranches.length + 1).padStart(3, '0')}`,
                    ...data,
                    status: 'Active',
                    customerCount: 0,
                    loanCount: 0
                };
                mockBranches = [...mockBranches, newBranch];
                resolve(newBranch);
            }, 500);
        });
    },

    // Update branch
    updateBranch: async (id: string, data: BranchFormData): Promise<Branch> => {
        return new Promise((resolve) => {
            setTimeout(() => {
                const index = mockBranches.findIndex(b => b.id === id);
                if (index !== -1) {
                    mockBranches[index] = { ...mockBranches[index], ...data };
                    resolve(mockBranches[index]);
                }
            }, 500);
        });
    },

    // Delete branch
    deleteBranch: async (id: string): Promise<void> => {
        return new Promise((resolve) => {
            setTimeout(() => {
                mockBranches = mockBranches.filter(b => b.id !== id);
                resolve();
            }, 500);
        });
    }
};
