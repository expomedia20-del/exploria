import { CalendarClock } from 'lucide-react';
import { useRef } from 'react';
import type { ComponentProps } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type DateTimePickerFieldProps = Omit<ComponentProps<'input'>, 'type'> & {
    error?: string;
    hint?: string | null;
    inputClassName?: string;
    label?: string;
    wrapperClassName?: string;
};

export function DateTimePickerField({
    className,
    error,
    hint = 'برای تعیین روز، ماه، سال و ساعت روی آیکون تقویم بزنید.',
    id,
    inputClassName,
    label,
    name,
    wrapperClassName,
    ...props
}: DateTimePickerFieldProps) {
    const inputRef = useRef<HTMLInputElement>(null);
    const fieldId = id ?? name;
    const pickerLabel = label ?? name ?? 'تاریخ';

    const openPicker = () => {
        const input = inputRef.current;

        if (!input) {
            return;
        }

        input.focus();

        const showPicker = (
            input as HTMLInputElement & { showPicker?: () => void }
        ).showPicker;

        if (showPicker) {
            showPicker.call(input);

            return;
        }

        input.click();
    };

    return (
        <div className={cn('grid gap-2', wrapperClassName)}>
            {label && fieldId ? <Label htmlFor={fieldId}>{label}</Label> : null}
            <div
                className={cn(
                    'flex overflow-hidden rounded-md border border-input bg-background shadow-xs focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50',
                    className,
                )}
            >
                <Input
                    ref={inputRef}
                    id={fieldId}
                    type="datetime-local"
                    name={name}
                    className={cn(
                        'border-0 shadow-none focus-visible:ring-0',
                        inputClassName,
                    )}
                    aria-invalid={Boolean(error)}
                    {...props}
                />
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className="h-9 shrink-0 rounded-none border-r border-input"
                    aria-label={`باز کردن تقویم ${pickerLabel}`}
                    title={`باز کردن تقویم ${pickerLabel}`}
                    onClick={openPicker}
                >
                    <CalendarClock className="size-4" />
                </Button>
            </div>
            {hint ? (
                <p className="text-xs text-muted-foreground">{hint}</p>
            ) : null}
            <InputError message={error} />
        </div>
    );
}
