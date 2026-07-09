import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            {...props}
            viewBox="0 0 64 64"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <path
                d="M32 6.5C19.85 6.5 10 16.35 10 28.5C10 43.8 32 58 32 58C32 58 54 43.8 54 28.5C54 16.35 44.15 6.5 32 6.5Z"
                fill="url(#exploria-pin)"
            />
            <path
                d="M23 36.5C26.5 41.5 37.5 41.5 41 36.5"
                stroke="white"
                strokeWidth="4"
                strokeLinecap="round"
            />
            <path
                d="M23.5 29.25L39.5 21.5L34.75 37.75L31.2 31.75L23.5 29.25Z"
                fill="white"
            />
            <path
                d="M32 12.5C23.16 12.5 16 19.66 16 28.5"
                stroke="#B8FFF2"
                strokeWidth="3"
                strokeLinecap="round"
                opacity="0.9"
            />
            <defs>
                <linearGradient
                    id="exploria-pin"
                    x1="12"
                    x2="54"
                    y1="10"
                    y2="54"
                    gradientUnits="userSpaceOnUse"
                >
                    <stop stopColor="#14B8A6" />
                    <stop offset="0.48" stopColor="#2563EB" />
                    <stop offset="1" stopColor="#F97316" />
                </linearGradient>
            </defs>
        </svg>
    );
}
