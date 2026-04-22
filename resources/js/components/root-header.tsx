import * as Avatar from '@radix-ui/react-avatar';
import * as DropdownMenu from '@radix-ui/react-dropdown-menu';
import * as NavigationMenu from '@radix-ui/react-navigation-menu';
import { Bell, ChevronDown, LogOut, Settings, User, Vote } from 'lucide-react';
import React from 'react';

// --- Types ---
interface UserProfile {
  name: string;
  email: string;
  avatarUrl?: string;
}

interface HeaderProps {
  user: UserProfile;
  onLogout: () => void;
}

// --- Component ---
export const Header: React.FC<HeaderProps> = ({ user, onLogout }) => {
  return (
    <header className="w-full bg-vote-primary px-6 py-3 shadow-md flex items-center justify-between sticky top-0 z-50">
      
      {/* Left: Brand / Logo */}
      <div className="flex items-center gap-2 text-white">
        <div className="p-2 bg-vote-accent rounded-md shadow-sm">
          <Vote size={24} className="text-white" />
        </div>
        <span className="text-xl font-bold tracking-wide">VoteManager</span>
      </div>

      {/* Center: Navigation Menu (Radix UI) */}
      <NavigationMenu.Root className="hidden md:flex relative justify-center w-full max-w-md">
        <NavigationMenu.List className="flex gap-1 p-1 bg-white/10 rounded-lg">
          
          <NavigationMenu.Item>
            <NavigationMenu.Link
              href="/dashboard"
              className="block px-4 py-2 rounded-md text-sm font-medium text-white hover:bg-vote-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-vote-accent"
            >
              Dashboard
            </NavigationMenu.Link>
          </NavigationMenu.Item>

          <NavigationMenu.Item>
            <NavigationMenu.Link
              href="/elections"
              className="block px-4 py-2 rounded-md text-sm font-medium text-white hover:bg-vote-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-vote-accent"
            >
              Elections
            </NavigationMenu.Link>
          </NavigationMenu.Item>

          <NavigationMenu.Item>
            <NavigationMenu.Link
              href="/voters"
              className="block px-4 py-2 rounded-md text-sm font-medium text-white hover:bg-vote-secondary transition-colors focus:outline-none focus:ring-2 focus:ring-vote-accent"
            >
              Voters
            </NavigationMenu.Link>
          </NavigationMenu.Item>

        </NavigationMenu.List>
      </NavigationMenu.Root>

      {/* Right: Actions & User Profile */}
      <div className="flex items-center gap-4">
        
        {/* Accent CTA Button */}
        <button className="hidden sm:flex items-center gap-2 bg-vote-accent hover:bg-sky-500 text-white px-4 py-2 rounded-md text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-vote-primary shadow-sm">
          <span>New Election</span>
        </button>

        {/* Notification Bell */}
        <button type='button' className="relative p-2 text-white hover:bg-vote-secondary rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-vote-accent">
          <Bell size={20} />
          <span className='hidden'>Notifications</span>
          <span className="absolute top-1 right-1 w-2.5 h-2.5 bg-vote-accent rounded-full border-2 border-vote-primary"></span>
        </button>

        {/* User Dropdown (Radix UI) */}
        <DropdownMenu.Root>
          <DropdownMenu.Trigger className="flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-vote-accent rounded-full p-1 transition-all hover:bg-vote-secondary">
            <Avatar.Root className="inline-flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-vote-secondary border-2 border-white/20">
              <Avatar.Image
                className="h-full w-full object-cover"
                src={user.avatarUrl}
                alt={user.name}
              />
              <Avatar.Fallback
                className="leading-1 flex h-full w-full items-center justify-center bg-white text-vote-primary font-bold text-sm"
                delayMs={600}
              >
                {user.name.charAt(0)}
              </Avatar.Fallback>
            </Avatar.Root>
            <ChevronDown size={16} className="text-white hidden sm:block" />
          </DropdownMenu.Trigger>

          <DropdownMenu.Portal>
            <DropdownMenu.Content
              className="min-w-55 bg-white rounded-md p-2 shadow-lg ring-1 ring-black ring-opacity-5 data-[side=top]:animate-slideDownAndFade data-[side=right]:animate-slideLeftAndFade data-[side=bottom]:animate-slideUpAndFade data-[side=left]:animate-slideRightAndFade z-50"
              sideOffset={8}
              align="end"
            >
              <div className="px-2 py-2 border-b border-gray-100 mb-2">
                <p className="text-sm font-medium text-gray-900">{user.name}</p>
                <p className="text-xs text-gray-500 truncate">{user.email}</p>
              </div>

              <DropdownMenu.Item className="group text-sm leading-none text-gray-700 rounded-md flex items-center h-9 px-2 relative select-none outline-none data-highlighted:bg-vote-secondary data-highlighted:text-white cursor-pointer transition-colors">
                <User className="mr-2 h-4 w-4" /> Profile
              </DropdownMenu.Item>
              
              <DropdownMenu.Item className="group text-sm leading-none text-gray-700 rounded-md flex items-center h-9 px-2 relative select-none outline-none data-highlighted:bg-vote-secondary data-highlighted:text-white cursor-pointer transition-colors">
                <Settings className="mr-2 h-4 w-4" /> System Settings
              </DropdownMenu.Item>

              <DropdownMenu.Separator className="h-px bg-gray-100 m-1" />

              <DropdownMenu.Item 
                onClick={onLogout}
                className="group text-sm leading-none text-red-600 rounded-md flex items-center h-9 px-2 relative select-none outline-none data-highlighted:bg-red-50 data-highlighted:text-red-700 cursor-pointer transition-colors"
              >
                <LogOut className="mr-2 h-4 w-4" /> Log out
              </DropdownMenu.Item>
              
            </DropdownMenu.Content>
          </DropdownMenu.Portal>
        </DropdownMenu.Root>
        
      </div>
    </header>
  );
};