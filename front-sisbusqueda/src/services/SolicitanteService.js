import { api } from "src/boot/axios";

class SolicitanteService {
    static async getData(params) {
        return (await api.get('/api/solicitantes',params)).data;
    }

    static async get(id) {
        return (await api.get(`/api/solicitantes/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/solicitantes/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/solicitantes", reg)).data;
        } else {
            return (await api.put(`/api/solicitantes/${reg.id}`, reg)).data;
        }
    }
}

export default SolicitanteService;
