import { Fragment } from 'react';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/react';
import { Link } from '@inertiajs/react';

const Dropdown = ({ children }) => (
    <Menu as="div" className="relative">
        {children}
    </Menu>
);

const Trigger = ({ children }) => (
    <MenuButton as={Fragment}>{children}</MenuButton>
);

const Content = ({
    align = 'right',
    width = '48',
    contentClasses = 'py-1 bg-white',
    children,
}) => {
    const widthClasses = width === '48' ? 'w-48' : '';

    return (
        <MenuItems
            anchor={{
                to: align === 'left' ? 'bottom start' : 'bottom end',
                gap: '8px',
            }}
            portal
            transition
            className={
                'z-50 rounded-md shadow-lg ring-1 ring-black/5 focus:outline-none ' +
                'transition duration-150 ease-out data-[closed]:scale-95 data-[closed]:opacity-0 ' +
                `${widthClasses} ${contentClasses}`
            }
        >
            {children}
        </MenuItems>
    );
};

const Item = ({ className = '', children, ...props }) => (
    <MenuItem as={Fragment}>
        <button
            type="button"
            {...props}
            className={
                'block w-full px-4 py-2 text-start text-sm transition focus:outline-none data-[focus]:bg-gray-100 hover:bg-gray-100 ' +
                className
            }
        >
            {children}
        </button>
    </MenuItem>
);

const DropdownLink = ({ className = '', children, ...props }) => (
    <MenuItem as={Fragment}>
        <Link
            {...props}
            className={
                'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 transition duration-150 ease-in-out focus:outline-none data-[focus]:bg-gray-100 hover:bg-gray-100 ' +
                className
            }
        >
            {children}
        </Link>
    </MenuItem>
);

Dropdown.Trigger = Trigger;
Dropdown.Content = Content;
Dropdown.Item = Item;
Dropdown.Link = DropdownLink;

export default Dropdown;
