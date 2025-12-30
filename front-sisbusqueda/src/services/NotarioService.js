import { api } from "src/boot/axios";

class NotarioService {

    static async getData(params) {
      try {
          const response = await api.get('/api/notarios', params);
          return response.data;
      } catch (error) {
          console.error("Error al obtener los datos de notarios:", error);
          throw error; // Lanza el error para manejarlo en el componente
      }
    }

  // Todos (sin paginación)
static async getAll() {
  try {
    const response = await api.get("/api/notarios", { params: { rowsPerPage: 0 } });
    return response.data.data; // 👈 devuelve directamente el array
  } catch (error) {
    console.error("Error al obtener todos los notarios:", error);
    throw error;
  }
}



    static async get(id) {
        return (await api.get(`/api/notarios/${id}`)).data;
    }

    static async delete(id) {
        return (await api.delete(`/api/notarios/${id}`));
    }

    static async save(reg) {
        if (reg.id === undefined || reg.id === null) {
            return (await api.post("/api/notarios", reg)).data;
        } else {
            return (await api.put(`/api/notarios/${reg.id}`, reg)).data;
        }
    }
}

export default NotarioService;
