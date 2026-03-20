<x-app-layout>
    <div style="max-width: 1280px; margin: 0 auto; padding: 40px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px;">
            <div>
                <h1 style="font-size: 1.875rem; font-weight: 700; font-family: var(--font-heading); color: var(--text-main);">Home Economy</h1>
                <p style="color: var(--text-muted);">Manage your household finances and shopping lists.</p>
            </div>
            <div style="padding: 8px 16px; background: var(--primary-soft); color: var(--primary); border-radius: 9999px; font-size: 0.875rem; font-weight: 700; font-family: var(--font-outfit);">
                March – May 2026
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
            <!-- Feature Cards -->
            <a href="#" class="card" style="display: block; text-decoration: none; padding: 24px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)'">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Shopping List</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Organize your weekly groceries and household needs.</p>
            </a>

            <a href="{{ route('economy.index') }}" wire:navigate class="card" style="display: block; text-decoration: none; padding: 24px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)'">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--success); color: white; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Budgeting</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Track income and expenses with ease.</p>
            </a>

            <a href="#" class="card" style="display: block; text-decoration: none; padding: 24px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-sm)'">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #9333ea; color: white; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3 style="font-size: 1.125rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Kids Rewards</h3>
                <p style="font-size: 0.875rem; color: var(--text-muted);">Managed points and rewards for the little ones.</p>
            </a>
        </div>
        </div>
    </div>
</x-app-layout>
