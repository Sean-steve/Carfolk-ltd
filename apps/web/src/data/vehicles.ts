export interface PreviewVehicle {
  id: string;
  name: string;
  make: string;
  model: string;
  year: number;
  bodyType: string;
  descriptor: string;
  image: string;
  imagePosition?: string;
}
export const previewVehicles: PreviewVehicle[] = [
  { id:'prado',name:'Toyota Land Cruiser Prado',make:'Toyota',model:'Land Cruiser Prado',year:2019,bodyType:'SUV',descriptor:'Premium 4×4 SUV',image:'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1100&q=85' },
  { id:'rav4',name:'Toyota RAV4',make:'Toyota',model:'RAV4',year:2021,bodyType:'SUV',descriptor:'Versatile everyday SUV',image:'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1100&q=85' },
  { id:'crv',name:'Honda CR-V',make:'Honda',model:'CR-V',year:2020,bodyType:'SUV',descriptor:'Room for every journey',image:'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1100&q=85' },
  { id:'cx5',name:'Mazda CX-5',make:'Mazda',model:'CX-5',year:2019,bodyType:'Crossover',descriptor:'Contemporary crossover',image:'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1100&q=85' },
];
