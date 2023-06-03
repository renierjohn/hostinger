import { createSlice, configureStore } from '@reduxjs/toolkit'

const Dragstates = createSlice({
  name: 'counter',
  initialState: {
    sidebar_data: [],
    component_data: [],
    value: 0,
  },
  reducers: {
    incremented: state => {
      state.value += 1
    },
    incrementByAmount: (state, action) => {
      console.log(action)
      state.value += action.payload
    },
    addState: (state, action) => {
      state.component_data.push(action.payload)
    },
    initSidebar: (state, action) => {
      state.sidebar_data = action.payload
    }
  }
})

export default Dragstates;
