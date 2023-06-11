import  Dragstates  from '../redux/DragStates';
import { configureStore } from '@reduxjs/toolkit'


const store = configureStore({
  reducer: Dragstates.reducer
})

store.subscribe(() => console.log(store.getState()))

export const ComponentBaseData = (param) => {
  console.log(param)
  store.dispatch(Dragstates.actions.addState(param))
  return param;
}

export const ComponentBaseSideBarData = (param) => {
  store.dispatch(Dragstates.actions.initSidebar(param))
  return param;
}

export const ComponentState = (param) => {
  return store.getState();
}

export default {
    ComponentState,
    ComponentBaseData,
    ComponentBaseSideBarData
}