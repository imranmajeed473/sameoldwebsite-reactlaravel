declare global {
    type PartialBy<T, K extends keyof T> = Omit<T, K> & Partial<Pick<T, K>>;
    type Overwrite<T1, T2> = {
        [P in Exclude<keyof T1, keyof T2>]: T1[P]
    } & T2;
    type PartialRecord<K extends string | number | symbol, T> = { [P in K]?: T; };
}

export default { };
