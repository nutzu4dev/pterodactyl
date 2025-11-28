import http from '@/api/http';

export default (uuid: string, name: string, map: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post(`/api/client/extensions/rustserverwiper/servers/${uuid}/wipe/map`, { name, map })
            .then(() => resolve())
            .catch(reject);
    });
};