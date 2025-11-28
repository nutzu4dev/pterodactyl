import http from '@/api/http';

export default (uuid: string, map: number): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.delete(`/api/client/extensions/rustserverwiper/servers/${uuid}/wipe/map/${map}`)
            .then(() => resolve())
            .catch(reject);
    });
};