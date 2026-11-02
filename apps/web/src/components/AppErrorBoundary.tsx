import {Component,type ReactNode} from 'react';

type Props={children:ReactNode};
type State={failed:boolean};

/** A safe, navigable fallback for unexpected React render crashes. */
export class AppErrorBoundary extends Component<Props,State>{
 state:State={failed:false};

 static getDerivedStateFromError():State{
  return {failed:true};
 }

 render(){
  if(!this.state.failed)return this.props.children;
  return <main className="app-error-fallback" role="alert">
   <div className="app-error-fallback__card">
    <span className="eyebrow eyebrow--red">CARFOLK LTD</span>
    <h1>We couldn't display this page</h1>
    <p>Something unexpected happened. You can reload the site or return to the showroom.</p>
    <div className="app-error-fallback__actions">
     <button type="button" onClick={()=>window.location.reload()}>Try again</button>
     <a href="/" >Return to Carfolk</a>
    </div>
   </div>
  </main>;
 }
}
